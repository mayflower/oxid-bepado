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
 * The most important controller for this module.
 *
 * It defines the route for the api to get the connection to bepado.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mfBepado extends oxUbase
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    public function render() {
        parent::render();

        /** @var mf_sdk_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $this->_aViewData['sdk_result'] = $helper->handleRequest();

        return 'mf_sdk_result.tpl';
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
