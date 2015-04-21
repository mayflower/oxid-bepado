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
 * This is the main converter. It works like a chain, that iterates over all known
 * converter and asks them if the support the current object combination.
 *
 * Actually the chain is filed in the constructor as we have no kind of DI in OXID.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductConverterChain extends mfAbstractConvertChain implements mfConverterInterface
{
    /**
     * Add your converters to the chain.
     */
    protected function initializeChain()
    {
        $this->converters = array(
            $this->getVersionLayer()->createNewObject('mfProductConverter'),
            $this->getVersionLayer()->createNewObject('mfProductBaseConverter'),
            $this->getVersionLayer()->createNewObject('mfProductPricingConverter'),
            $this->getVersionLayer()->createNewObject('mfProductAttributesConverter'),
            $this->getVersionLayer()->createNewObject('mfProductDeliveryConverter'),
            $this->getVersionLayer()->createNewObject('mfProductImagesConverter'),
            $this->getVersionLayer()->createNewObject('mfProductCategoriesConverter'),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function fromShopToBepado($shopObject, $bepadoObject, $params = null)
    {
        foreach ($this->converters as $converter) {
            $converter->setVersionLayer($this->getVersionLayer());
            $converter->fromShopToBepado($shopObject, $bepadoObject, $params);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function fromBepadoToShop($bepadoObject, $shopObject, $params = null)
    {
        foreach ($this->converters as $converter) {
            $converter->setVersionLayer($this->getVersionLayer());
            $converter->fromBepadoToShop($bepadoObject, $shopObject, $params);
        }
    }
}
