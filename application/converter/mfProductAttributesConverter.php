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
class mfProductAttributesConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * Mapping Array for units.
     *
     * @var array
     */
    protected $oxidUnitMapper;

    public function __construct()
    {
        $result = $this->getVersionLayer()->getDb(true)->getAll('SELECT * FROM mfbepadounits');
        foreach ($result as $row) {
            $this->oxidUnitMapper[$row['OXID']] = $row['BEPADOUNITKEY'];
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param oxArticle $shopObject
     * @param Product $bepadoObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {
        $bepadoObject->availability = (int) $shopObject->oxarticles__oxstock->value;

        $sDimension = sprintf(
            '%sx%sx%s',
            $shopObject->oxarticles__oxlength->value,
            $shopObject->oxarticles__oxwidth->value,
            $shopObject->oxarticles__oxheight->value
        );
        $size = $shopObject->oxarticles__oxlength->value *
            $shopObject->oxarticles__oxwidth->value *
            $shopObject->oxarticles__oxheight->value;

        $bepadoObject->attributes = array(
            Product::ATTRIBUTE_WEIGHT => $shopObject->oxarticles__oxweight->value,
            Product::ATTRIBUTE_VOLUME => (string) $size,
            Product::ATTRIBUTE_DIMENSION => $sDimension,
            // reference quantity is always 1 in oxid shop
            Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Product::ATTRIBUTE_QUANTITY => $shopObject->oxarticles__oxunitquantity->value,
        );

        if (isset($this->oxidUnitMapper[$shopObject->oxarticles__oxunitname->value])) {
            $bepadoObject->attributes[Product::ATTRIBUTE_UNIT] = $this->oxidUnitMapper[$shopObject->oxarticles__oxunitname->value];
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
        $aParams = array();
        $aParams['oxarticles__oxstock'] = $bepadoObject->availability;

        if (isset($bepadoObject->attributes[Product::ATTRIBUTE_UNIT])) {
            $aUnitMapping = array_flip($this->oxidUnitMapper);
            if (isset($aUnitMapping[$bepadoObject->attributes[Product::ATTRIBUTE_UNIT]])) {
                $aParams['oxarticles__oxunitname'] = $aUnitMapping[$bepadoObject->attributes[Product::ATTRIBUTE_UNIT]];
            }
        }

        $aParams['oxarticles__oxunitquantity'] = $bepadoObject->attributes[Product::ATTRIBUTE_QUANTITY];
        $aParams['oxarticles__oxweight'] = $bepadoObject->attributes[Product::ATTRIBUTE_WEIGHT];

        $aDimension = explode('x', $bepadoObject->attributes[Product::ATTRIBUTE_DIMENSION]);
        $aParams['oxarticles__oxlength'] = $aDimension[0];
        $aParams['oxarticles__oxwidth'] = $aDimension[1];
        $aParams['oxarticles__oxheight'] = $aDimension[2];

        $shopObject->assign($aParams);
    }
}
