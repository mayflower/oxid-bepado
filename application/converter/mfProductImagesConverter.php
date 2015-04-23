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
 * Converter to map images from an bepado product and back.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductImagesConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * {@inheritDoc}
     *
     * Just adds the picture url to the list of the bepado product.
     *
     * @param oxArticle $shopObject
     * @param Product   $bepadoObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {
        for ($i = 1; $i <= 12; $i++) {
            if ($shopObject->getFieldData("oxarticles__oxpic$i")) {
                $bepadoObject->images[] = $shopObject->getPictureUrl($i);
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * Creates an image in the own shop by a helper.
     *
     * @param Product   $bepadoObject
     * @param oxArticle $shopObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $aParams = array();

        foreach ($bepadoObject->images as $key => $imagePath) {
            if ($key < 12){
                try {
                    list($fieldName, $fieldValue) = $sdkHelper->createOxidImageFromPath($imagePath, $key+1);
                    $aParams[$fieldName] = $fieldValue;
                } catch (\Exception $e) {
                    $this->getLogger()->writeBepadoLog(
                        sprintf(
                            'Image %s could not be saved during product conversion. Got the following exception: %s',
                            $imagePath,
                            $e->getMessage()
                        )
                    );
                }
            }
        }

        $shopObject->assign($aParams);
    }
}
