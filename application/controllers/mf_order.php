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
 * The modules extension of the oxid order view/controller.
 *
 * We need to step into the following points:
 *
 *  - render the order to have a chance to check bepado product states
 *  - finalizeOrder to reserver and checkout the optional bepado products
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_order extends mf_order_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * When rendering an order view, we need to check the imported articles
     * by the SDK's checkProduct().
     *
     * @return string
     */
    public function render()
    {
        $parent = parent::render();

        $oxBasket = $this->getBasket();

        /** @var mf_sdk_product_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_product_helper');
        $helper->checkProductsInBasket($oxBasket);

        return $parent;
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
}
