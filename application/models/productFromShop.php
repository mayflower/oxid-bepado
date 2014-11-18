<?php

use Bepado\SDK\ProductFromShop;
use Bepado\SDK\SDK;
use Bepado\SDK\SecurityException;
use Bepado\SDK\Struct\Address;
use Bepado\SDK\Struct\Order;
use Bepado\SDK\Struct\OrderItem;

class oxidProductFromShop implements ProductFromShop
{
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
        $oModuleSDKConverter = oxNew('mf_sdk_converter');

        foreach ($ids as $id) {
            // load oxid article
            /**
             * @var oxarticle $oxProduct
             */
            $oxProduct = oxNew('oxarticle');
            $oxProduct->load($id);

            if (!$oxProduct->readyForExportToBepado()) {
                continue;
            }

            $sdkProducts[] = $oModuleSDKConverter->toBepadoProduct($oxProduct);
        }

        return $sdkProducts;
    }

    public function getShopProducts(array $ids)
    {
        $shopProducts = array();

        foreach ($ids as $id) {
            // load oxid article
            /**
             * @var oxarticle $oxProduct
             */
            $oxProduct = oxNew('oxarticle');
            $oxProduct->load($id);

            if (!$oxProduct->readyForExportToBepado() || !$oxProduct->isLoaded()) {
                continue;
            }

            $shopProducts[] = $oxProduct;
        }

        return $shopProducts;
    }

    /**
     * @throws \BadMethodCallException
     *
     * @return string[]|void
     */
    public function getExportedProductIDs()
    {
        throw new \BadMethodCallException('Not needed in oxid module.');
    }

    /**
     * @param Order $order
     */
    public function reserve(Order $order)
    {
        // not using explicit reservation handling.
    }

    /**
     * @param Order $order
     *
     * @return string
     */
    public function buy(Order $order)
    {
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = oxNew('mf_sdk_helper');
        $sdkConfig = $sdkHelper->createSdkConfigFromOxid();
        $sdk = $sdkHelper->instantiateSdk($sdkConfig);

        // create user and "login" - create a session entry
        $shopUser = $this->getOrCreateUser($order, $sdk);

        // assign the delivery address to the shop user
        $deliveryAddress = $this->convertAddress($order->deliveryAddress);
        $oxDeliveryAddress = oxNew('oxaddress');
        $oxDeliveryAddress->assign($deliveryAddress);
        $oxDeliveryAddress->oxaddress__oxuserid = new oxField($shopUser->getId(), oxField::T_RAW);
        $oxDeliveryAddress->oxaddress__oxcountry = $shopUser->getUserCountry($deliveryAddress['oxaddress__oxcountry']);
        $oxDeliveryAddress->save();
        $_POST['sDeliveryAddressMD5'] = $shopUser->getEncodedDeliveryAddress().$oxDeliveryAddress->getEncodedDeliveryAddress();
        $_POST['deladrid'] = $oxDeliveryAddress->getId();

        // add all order items to a basket
        $oxBasket = $this->getSession()->getBasket();
        $this->addToBasket($order->orderItems, $oxBasket);

        $oxPaymentID = $this->createPaymentID($order);
        if (!$oxPaymentID) {
            return false;
        }

        $oxBasket->setPayment($oxPaymentID);

        $oxOrder = oxNew('oxorder');
        try {
            $iSuccess = $oxOrder->finalizeOrder($oxBasket, $shopUser);

            $shopUser->onOrderExecute($oxBasket, $iSuccess);

            return $oxOrder->getId();
        } catch (\Exception $e) {
            return false;
        }
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
        $oxCountry = oxRegistry::get('oxcountry');
        $select = $oxCountry->buildSelectString(array('OXISOALPHA3' => $address->country, 'OXACTIVE' => 1));
        $countryID = oxDb::getDb(true)->getOne($select);
        $oxCountryId = $countryID ?: null;

        $oxState = oxRegistry::get('oxstate');
        $select = $oxState->buildSelectString(array('OXTITLE' => $address->state));
        $stateID = oxDb::getDb(true)->getOne($select);
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
        $oxPayment = oxRegistry::get('oxpayment');
        $select = $oxPayment->buildSelectString(array('bepadopaymenttype' => $order->paymentType));
        $paymentID = oxDb::getDb(true)->getOne($select);

        return $paymentID ?: null;
    }

    /**
     * By using the convertet this method creates a set of oxArticles.
     *
     * @param OrderItem[]|array $orderItems
     *
     * @param oxBasket $oxBasket
     *
     * @throws Exception
     * @throws null
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     * @throws oxOutOfStockException
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


        $oxProducts = $this->getShopProducts(array_keys($products));
        foreach ($oxProducts as $oxProduct) {
            $amount = $products[$oxProduct->getId()]['count'];
            $oxBasket->addToBasket($oxProduct->getId(), $amount);
            $oxBasket->calculateBasket(true);
        }
    }

    /**
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

        $oxGroup = oxNew('oxgroups');
        if (!$oxGroup->load(self::BEPADO_USERGROUP_ID)) {
            throw new \RuntimeException('No user group for bepado remote shop found.');
        }

        /** @var oxUser $oxUser */
        $oxUser = oxNew('oxuser');
        $select = $oxUser->buildSelectString(array('bepadoshopid' => $shopId, 'OXACTIVE' => 1));
        $oxUser->assignRecord($select);

        // creates the shop as an user if it does not exist
        if (!$oxUser->isLoaded()) {
            $values = array(
                'oxuser__oxusername' => $shopId,
                'oxuser__oxurl'      => $shop->url,
                'bepadoshopid'       => $shopId,
                'oxuser__oxactive'   => true,
            );
            $values = array_merge($values, $this->convertAddress($order->billingAddress, 'oxuser__ox'));
            $oxUser->assign($values);
            $oxUser->addToGroup(self::BEPADO_USERGROUP_ID);

            $oxUser->save();
        }

        return $oxUser;
    }

    /**
     * Creates the session object.
     *
     * @return oxSession
     */
    private function getSession()
    {
        return oxRegistry::getSession();
    }

    /**
     * @return oxbasket
     */
    private function getBasket()
    {
        return $this->getSession()->getBasket();
    }

    /**
     * @return oxConfig
     */
    private function getConfig()
    {
        return oxRegistry::getConfig();
    }
}

