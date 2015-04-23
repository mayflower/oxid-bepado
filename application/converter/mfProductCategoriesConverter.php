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
class mfProductCategoriesConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * {@inheritDoc}
     *
     * @param oxArticle $shopObject
     * @param Product   $bepadoObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {
        $aIds = $shopObject->getCategoryIds();
        $bepadoCategories = $this->getVersionLayer()->createNewObject('oxList');
        $bepadoCategories->init('oxbase', 'bepado_categories');
        $bepadoCategories->getBaseObject();
        $bepadoCategories->getList();
        $bepadoCategories = $bepadoCategories->getArray();

        foreach ($aIds as $oxidCategoryId) {
            $matchingCategory = array_filter($bepadoCategories, function($category) use ($oxidCategoryId) {
                return $category->getFieldData('bepado_categories__catnid') == $oxidCategoryId;
            });
            if (count($matchingCategory) === 1) {
                $category = array_shift($matchingCategory);
                $bepadoObject->categories[] = $category->getFieldData('bepado_categories__title');
            }
        }

    }

    /**
     * {@inheritDoc}
     *
     * @param Product   $bepadoObject
     * @param oxArticle $shopObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {
        // TODO: Implement fromBepadoToShop() method with the change for the category mapping.
    }
}
