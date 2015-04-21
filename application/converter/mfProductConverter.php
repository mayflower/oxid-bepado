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
 * Base converter, which converts oxArticles into SDK Products and back.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mfProductConverter implements mfConverterInterface
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * {@inheritDoc}
     *
     * @param oxArticle $shopObject
     * @param Product   $bepadoObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {

    }

    /**
     * {@inheritDoc}
     *
     * @param Product   $bepadoObject
     * @param oxArticle $shopObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {

    }

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    private function getVersionLayer()
    {
        if (null == $this->_oVersionLayer) {
            /** @var VersionLayerFactory $factory */
            $factory = oxNew('VersionLayerFactory');
            $this->_oVersionLayer = $factory->create();
        }

        return $this->_oVersionLayer;
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }
}
