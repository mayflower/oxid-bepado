<?php

use Bepado\SDK\ProductFromShop;
use Bepado\SDK\SDK;
use Bepado\SDK\SecurityException;
use Bepado\SDK\Struct\Address;
use Bepado\SDK\Struct\Order;
use Bepado\SDK\Struct\OrderItem;

class oxidProductFromShop implements ProductFromShop
{
    /**
    * @var VersionLayerInterface
    */
    private $_oVersionLayer;

    const BEPADO_USERGROUP_ID = 'bepadoshopgroup';

    /**
     * @param array $ids
     *
     * @return array|\Bepado\SDK\Struct\Product[]
     */
    public function getProducts(array $ids)
    {
        $sdkProducts = array();
        /** @var mf_sdk_converter $oModuleSDKConverter */
        $oModuleSDKConverter = $this->_oVersionLayer->createNewObject('mf_sdk_converter');

        foreach ($ids as $id) {
            /** @var oxarticle $oxArticle */
            $oxArticle = $this->_oVersionLayer->createNewObject('oxarticle');
            $load = $oxArticle->load($id);

            if (!$load || !$oxArticle->readyForExportToBepado()) {
                continue;
            }

            $sdkProducts[] = $oModuleSDKConverter->toBepadoProduct($oxArticle);
        }

        return $sdkProducts;
    }

    /**
     * @throws \BadMethodCallException
     *
     * @return string[]|void
     */
    public function getExportedProductIDs()
    {
        $ids = array();
        $sql = "SELECT p_source_id FROM bepado_product_state WHERE state = ".SDKConfig::ARTICLE_STATE_EXPORTED;
        $oxDB = $this->_oVersionLayer->getDb(true);
        $list = $oxDB->execute($sql);

        while (!$list->EOF) {
            $ids[] = $list->fields[0];
            $list->MoveNext();
        }

        return $ids;
    }

    /**
     * Oxid does not support reservations at all, so we will just check the order
     * and validates its stocks.
     *
     * @param Order $order
     *
     * @throws Exception
     */
    public function reserve(Order $order)
    {
        /** @var oxBasket $oxBasket */
        $oxBasket = $this->_oVersionLayer->createNewObject('oxbasket');
        $this->addToBasket($order->orderItems, $oxBasket);
        if ($oxBasket->getProductsCount() === 0) {
            throw new Exception('No valid products in basket');
        }

        /** @var oxOrder $oxOrder */
        $oxOrder = $this->_oVersionLayer->createNewObject('oxorder');
        $stockValidation = $oxOrder->validateStock($oxBasket);

        if (!$stockValidation) {
            throw new Exception('Stock of articles is not valid');
        }
    }

    /**
     * Bepado sends an order object. Depending on that we
     * will fetch the products from its OrderItems, create a user
     * out for the remote shop.
     *
     * With the mapped payment method and the shipping costs a basket
     * will be created which will finalize an order inside of oxid.
     *
     * @param Order $order
     *
     * @throws Exception
     *
     * @return string
     */
    public function buy(Order $order)
    {
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $sdkConfig = $sdkHelper->createSdkConfigFromOxid();
        $sdk = $sdkHelper->instantiateSdk($sdkConfig);

        // create user and "login" - create a session entry
        $shopUser = $this->getOrCreateUser($order, $sdk);

        /** @var oxBasket $oxBasket */
        $oxBasket = $this->getVersionLayer()->createNewObject('oxbasket');
        $this->addToBasket($order->orderItems, $oxBasket);
        if ($oxBasket->getProductsCount() === 0) {
            throw new Exception('No valid products in basket');
        }

        // create and set the payment method
        $oxPaymentID = $this->createPaymentID($order);
        if (!$oxPaymentID) {
            throw new Exception('No Payment method found.');
        }
        $oxBasket->setPayment($oxPaymentID);

        // create shipping costs
        $shippingCosts = $sdk->calculateShippingCosts($order);
        $oxBasket->setDeliveryPrice($shippingCosts->shippingCosts);
        /** @var oxPrice $oxPrice */
        $oxPrice = $this->getVersionLayer()->createNewObject('oxprice');
        if (0 === $shippingCosts->shippingCosts) {
            $oxPrice->setPrice(0);
        } else {
            $oxPrice->setPrice(
                $shippingCosts->grossShippingCosts,
                ((100*$shippingCosts->grossShippingCosts/$shippingCosts->shippingCosts))/100 - 1
            );
        }
        $oxBasket->setCost('oxdelivery', $oxPrice);
        /** @var oxOrder $oxOrder */
        $oxOrder = $this->getVersionLayer()->createNewObject('oxorder');

        // finalize order and do a cleanup
        $iSuccess = $oxOrder->finalizeOrder($oxBasket, $shopUser);
        $shopUser->onOrderExecute($oxBasket, $iSuccess);
        $this->cleanUp();

        return $oxOrder->getId();
    }

    /**
     * Oxid and Bepado got two different address types: delivery and bill. both got an
     * almost equal structure. So we just transform them in here.
     *
     * @param Address $address
     * @param string  $type
     *
     * @return array
     */
    private function convertAddress(Address $address, $type = 'oxaddress__ox')
    {
        $oxCountry = oxNew('oxcountry');
        $select = $oxCountry->buildSelectString(array('OXISOALPHA3' => $address->country, 'OXACTIVE' => 1));
        $countryID = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oxCountryId = $countryID ?: null;

        $oxState = oxNew('oxstate');
        $select = $oxState->buildSelectString(array('OXTITLE' => $address->state));
        $stateID = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oxStateId = $stateID ?: null;

        $oxAddress = array(
            $type.'company'   => $address->company,
            $type.'fname'     => $address->firstName.(
                strlen($address->middleName) > 0
                    ? ' '.$address->middleName
                    : ''
                ),
            $type.'lname'      => $address->surName,
            $type.'street'    => $address->street,
            $type.'streetnr'  => $address->streetNumber,
            $type.'addinfo'   => $address->additionalAddressLine,
            $type.'ustid'     => null,
            $type.'city'      => $address->city,
            $type.'countryid' => $oxCountryId,
            $type.'stateid'   => $oxStateId,
            $type.'zip'       => $address->zip,
            $type.'fax'       => null,
            $type.'sal'       => null,
            $type.'fon'       => $address->phone,
            $type.'email'     => $address->email,
        );

        return $oxAddress;
    }

    /**
     * Create the payment ID from the mapped payment data.
     *
     * @param Order $order
     *
     * @return array
     */
    private function createPaymentID(Order $order)
    {
        $oxPayment = $this->_oVersionLayer->createNewObject('oxpayment');
        $select = $oxPayment->buildSelectString(array('bepadopaymenttype' => $order->paymentType));
        $paymentID = $this->_oVersionLayer->getDb(true)->getOne($select);

        return $paymentID ?: null;
    }

    /**
     * By using the convertet this method creates a set of oxArticles.
     *
     * @param OrderItem[]|array $orderItems
     * @param oxBasket $oxBasket
     *
     * @throws Exception
     * @throws null
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     * @throws oxOutOfStockException
     *
     * @return array|oxArticle[]
     */
    private function addToBasket(array $orderItems, oxBasket $oxBasket)
    {
        $products = array();
        foreach ($orderItems as $item) {
            $products[$item->product->sourceId] = array(
                'product' => $item->product,
                'count'   => $item->count,
            );
        }


        $oxProducts = $this->getProducts(array_keys($products));
        foreach ($oxProducts as $oxProduct) {
            $amount = $products[$oxProduct->getId()]['count'];
            $oxBasket->addToBasket($oxProduct->getId(), $amount);
            $oxBasket->calculateBasket(true);
        }
    }

    /**
     * When a shop is registered as an user it will be fetched from database, created if not.
     * The bepado bill address will be used as the users address, the delivery address added to the user.
     *
     * @param Order $order
     * @param SDK $sdk
     *
     * @return oxUser
     */
    private function getOrCreateUser(Order $order, SDK $sdk)
    {
        $shopId = $order->providerShop;
        $shop = $sdk->getShop($shopId);

        if (!$shop) {
            throw new SecurityException(sprintf('Shop with id %s not known', $shopId));
        }

        $oxGroup = $this->_oVersionLayer->createNewObject('oxgroups');
        if (!$oxGroup->load(self::BEPADO_USERGROUP_ID)) {
            throw new \RuntimeException('No user group for bepado remote shop found.');
        }

        /** @var oxUser $shopUser */
        $shopUser = $this->_oVersionLayer->createNewObject('oxuser');
        $select = $shopUser->buildSelectString(array('bepadoshopid' => $shopId, 'OXACTIVE' => 1));
        $shopUser->assignRecord($select);

        // creates the shop as an user if it does not exist
        if (!$shopUser->isLoaded()) {
            $values = array(
                'oxuser__oxusername' => $shopId,
                'oxuser__oxurl'      => $shop->url,
                'bepadoshopid'       => $shopId,
                'oxuser__oxactive'   => true,
            );
            $values = array_merge($values, $this->convertAddress($order->billingAddress, 'oxuser__ox'));
            $shopUser->assign($values);
            $shopUser->addToGroup(self::BEPADO_USERGROUP_ID);

            $shopUser->save();
        }

        // assign the delivery address to the shop user
        $deliveryAddress = $this->convertAddress($order->deliveryAddress);
        $oxDeliveryAddress = oxNew('oxaddress');
        $oxDeliveryAddress->assign($deliveryAddress);
        $oxDeliveryAddress->oxaddress__oxuserid = new oxField($shopUser->getId(), oxField::T_RAW);
        $oxDeliveryAddress->oxaddress__oxcountry = $shopUser->getUserCountry($deliveryAddress['oxaddress__oxcountry']);
        $oxDeliveryAddress->save();
        $_POST['sDeliveryAddressMD5'] = $shopUser->getEncodedDeliveryAddress().$oxDeliveryAddress->getEncodedDeliveryAddress();
        $_POST['deladrid'] = $oxDeliveryAddress->getId();

        return $shopUser;
    }

    /**
     * Some clean ups after the buy process
     */
    private function cleanUp()
    {
        $this->getVersionLayer()->getSession()->delBasket();
        unset($_POST['sDeliveryAddressMD5'], $_POST['deladrid']);
    }

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    private function getVersionLayer()
    {
        if (null == $this->_oVersionLayer) {
            /** @var VersionLayerFactory $factory */
            $factory = oxNew('VersionLayerFactory');
            $this->_oVersionLayer = $factory->create();
        }

        return $this->_oVersionLayer;
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }
}

