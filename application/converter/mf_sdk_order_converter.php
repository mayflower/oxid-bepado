<?php

use Bepado\SDK\Struct as Struct;

/**
 * Converter class to transport the oxid order data into a sdk order object and back.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_order_converter extends mf_abstract_converter implements mf_converter_interface
{
    /**
     * {@inheritDoc}
     *
     * Creates a bepado order object from the information given by a oxOrder.
     *
     * @param oxOrder $object
     *
     * @return Struct\Order
     */
    public function fromShopToBepado($object)
    {
        $sdkOrder = new Struct\Order();
        $oxBasket = $object->getBasket();

        return $sdkOrder;
    }

    /**
     * {@inheritDoc}
     *
     * @param Struct\Order $object
     *
     * @return oxOrder
     */
    public function fromBepadoToShop($object)
    {
        $oxOrder = oxNew('oxOrder');
        $parameters = array();
        /** @var mf_sdk_address_converter $addressConverter */
        $addressConverter = $this->getVersionLayer()->createNewObject('mf_sdk_address_converter');

        $parameters = array_merge($parameters, $addressConverter->fromBepadoToShop($object->billingAddress, 'oxarticles_oxbill'));
        $parameters = array_merge($parameters, $addressConverter->fromBepadoToShop($object->deliveryAddress, 'oxarticles_oxdel'));

        $oxOrder->assign($parameters);

        return $oxOrder;
    }
}
