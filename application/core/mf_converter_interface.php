<?php

/**
 * There are different situations where to convert objects from
 * shop to a bepado representation and/or back. This interface
 * gives us a common interface for that.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
interface mf_converter_interface
{
    /**
     * @param object $object
     *
     * @return object
     */
    public function fromShopToBepado($object);

    /**
     * @param object $object
     *
     * @return object
     */
    public function fromBepadoToShop($object);
}
