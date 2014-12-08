<?php

use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_order_converter implements mf_converter_interface
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

        return $oxOrder;
    }
}
