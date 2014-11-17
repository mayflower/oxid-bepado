<?php

use Bepado\SDK\ProductFromShop;
use Bepado\SDK\Struct\Address;
use Bepado\SDK\Struct\Order;

class oxidProductFromShop implements ProductFromShop
{
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
        // Hier muss die Bepado Order in eine Oxid Bestellung umgewandelt
        // werden. Rückgabewert ist die ID der Bestellung
        //
        $oxOrder = oxNew('oxorder'); // ??
        $products = $order->orderItems;
        $sdkShop = $order->orderShop;

        $orderValues = array();
        array_merge($orderValues, $this->convertAddress($order->billingAddress, 'bill'));
        array_merge($orderValues, $this->convertAddress($order->deliveryAddress, 'del'));

        // set shop id or handle for that
        // check if the providing shop is the current one
        // handle provided id????


        $oxOrder = oxNew('oxorder');
        $oxOrder->assign($orderValues);

        return $oxOrder->getOxID();
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
    private function convertAddress(Address $address, $type)
    {
        $oxCountry = oxRegistry::get('oxcountry');
        $select = $oxCountry->buildSelectString(array('OXISOALPHA3' => $address->country, 'OXACTIVE' => 1));
        $result =$oxCountry->assignRecord($select);
        $oxCountryId = $result ? $result->getOxID(): null;

        $oxState = oxRegistry::get('oxstate');
        $select = $oxState->buildSelectString(array('OXTITLE' => $address->state));
        $result = $oxState->assignRecord($select);
        $oxStateId = $oxState ? $result->getOxID() : null;

        $oxAddress = array(
            'oxorder__ox'.$type.'company'   => $address->company,
            'oxorder__oxxbilfname'     => $address->firstName.(
                strlen($address->middleName) > 0
                    ? ' '.$address->middleName
                    : ''
                ),
            'oxorder__ox'.$type.'name'      => $address->surName,
            'oxorder__ox'.$type.'street'    => $address->street,
            'oxorder__ox'.$type.'streetnr'  => $address->streetNumber,
            'oxorder__ox'.$type.'addinfo'   => $address->additionalAddressLine,
            'oxorder__ox'.$type.'ustid'     => null,
            'oxorder__ox'.$type.'city'      => $address->city,
            'oxorder__ox'.$type.'countryid' => $oxCountryId,
            'oxorder__ox'.$type.'stateid'   => $oxStateId,
            'oxorder__ox'.$type.'zip'       => $address->zip,
            'oxorder__ox'.$type.'fax'       => null,
            'oxorder__ox'.$type.'sal'       => null,
        );

        return $oxAddress;
    }
}

