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

use Bepado\SDK\Struct\Product;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductVendorConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * {@inheritDoc}
     *
     * Sets the articles vendor or the the shop name.
     *
     * @param Product $bepadoObject
     * @param oxArticle $shopObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {
        if (null !== $shopObject->getVendor()) {
            $bepadoObject->vendor = $shopObject->getVendor()->getFieldData('oxvendor__oxtitle');
        } else {
            $oShop = $this->getVersionLayer()->createNewObject('oxShop');
            $oShop->load($this->getVersionLayer()->getConfig()->getShopId());
            $bepadoObject->vendor = $oShop->getFieldData('oxshops__oxname');
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param oxArticle $shopObject
     * @param Product $bepadoObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {
        // TODO: Implement with #132.
    }
}
