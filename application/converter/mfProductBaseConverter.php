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
 * Base product converter, which is responsible to convert title, descriptions, url and ean.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductBaseConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * {@inheritDoc}
     *
     * @param oxArticle $shopObject
     * @param Product   $bepadoObject
     *
     * @return object
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {
        $bepadoObject->sourceId = $shopObject->getId();
        $bepadoObject->ean = $shopObject->oxarticles__oxean->value;
        $bepadoObject->url = $shopObject->getLink();
        $bepadoObject->title = $shopObject->oxarticles__oxtitle->value;
        $bepadoObject->shortDescription = $shopObject->oxarticles__oxshortdesc->value;
        $bepadoObject->longDescription = $shopObject->getLongDescription()->getRawValue();
    }

    /**
     * {@inheritDoc}
     *
     * @param Product   $bepadoObject
     * @param oxArticle $shopObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {
        $aParams['oxarticles__oxean'] = $bepadoObject->ean;
        $aParams['oxarticles__oxexturl'] = $bepadoObject->url;
        $aParams['oxarticles__oxtitle'] = $bepadoObject->title;
        $aParams['oxarticles__oxshortdesc'] = $bepadoObject->shortDescription;

        $shopObject->setArticleLongDesc($bepadoObject->longDescription);
        $shopObject->assign($aParams);
    }
}
