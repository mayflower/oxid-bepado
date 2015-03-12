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
 * This extension add additional information to the category list.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_category_list extends mf_category_list_parent
{
    /**
     * A category with a special mapping to a bepado category will
     * get this one as an entry in a new column in the category list view.
     *
     * An other template is used for this list.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $bepadoCategories = oxNew('oxlist');
        $bepadoCategories->init('oxbase', 'bepado_categories');
        $bepadoCategories->getBaseObject();
        $bepadoCategories->getList();
        $bepadoCategories = $bepadoCategories->getArray();

        foreach ($this->_aViewData['mylist'] as $key => $value) {
            // the bepado categories got an extra field (catnid) for the mapped oxid categories
            $matchingCategory = array_filter($bepadoCategories, function($category) use ($key) {
                return $category->getFieldData('bepado_categories__catnid') == $key;
            });
            if (count($matchingCategory) === 1) {
                $category = array_shift($matchingCategory);
                $value->oxcategories__bepadocategory = new oxField(
                    $category->bepado_categories__title->rawValue,
                    oxField::T_TEXT
                );
            }
        }

        return 'mf_category_list.tpl';
    }
}
