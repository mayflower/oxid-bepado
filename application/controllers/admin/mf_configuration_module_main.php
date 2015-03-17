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
 * This controller will render the main view of the module setting admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_module_main extends oxAdminDetails
{
    /**
     * @var VersionLayerInterface
     */
    protected $_oVersionLayer;

    /**
     * Flag if the shop is verified at bepado.
     *
     * @var bool
     */
    private $isVerified = false;

    /**
     * Simple constructor, to set a base value.
     */
    public function __construct()
    {
        parent::__construct();
        $this->isVerified = null;
    }

    /**
     * Prepares rendering of the main tab with the values for the template.
     *
     * In this case the model is fetched from database only based on the id.
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $oBepadoConfiguration = $this->getVersionLayer()->createNewObject('mfBepadoConfiguration');
        $this->_aViewData['edit'] = $oBepadoConfiguration;

        $oxId = $this->getEditObjectId();
        if ($oxId && '-1' !== $oxId) {
            $oBepadoConfiguration->load($oxId);
        }

        $this->_aViewData['verified'] = $this->isVerified;
        $this->_aViewData['available_purchaseGroups'] = $this->getAvailablePurchaseGroups();

        return 'mf_configuration_module_main.tpl';
    }

    /**
     * Persists the given values on the main tab.
     */
    public function save()
    {
        parent::save();

        $aParams = $this->getVersionLayer()->getConfig()->getRequestParameter("editval");

        $oBepadoConfiguration = $this->getVersionLayer()->createNewObject('mfBepadoConfiguration');
        $oBepadoConfiguration->load($this->getEditObjectId());
        $oBepadoConfiguration->assign($aParams);
        $oBepadoConfiguration->save();

        // set oxid if inserted
        $this->setEditObjectId($oBepadoConfiguration->getId());

        // verify the persisted configuration by its api key
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $apiEndpointUrl = $oShopConfig->getShopUrl().mfBepadoConfiguration::API_ENDPOINT_URL_SUFFIX;
        $oBepadoConfiguration->setApiEndpointUrl($apiEndpointUrl);
        $this->isVerified = $this
            ->getVersionLayer()
            ->createNewObject('mf_module_helper')
            ->verifyAtSdk($oBepadoConfiguration);

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
     * Creates the available fields to map a purchase price to.
     *
     * @return array
     */
    public function getAvailablePurchaseGroups()
    {
        return array('A', 'B', 'C');
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }
}
