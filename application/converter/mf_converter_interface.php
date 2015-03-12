<?php

/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

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
     * Method to convert from a model in the OXID eShop for example
     *
     * - oxArticle
     * - oxAddress
     * - oxOrder
     *
     * to a SDK model like
     *
     * - Product
     * - Order
     * - OrderItem
     *
     * @param object $object
     *
     * @return object
     */
    public function fromShopToBepado($object);

    /**
     * Method to convert from a model in the bepado SDK for example
     *
     * - Product
     * - Order
     * - OrderItem
     *
     * to a OXID eShop model like
     *
     * - oxArticle
     * - oxAddress
     * - oxOrder
     *
     * @param object $object
     *
     * @return object
     */
    public function fromBepadoToShop($object);
}
