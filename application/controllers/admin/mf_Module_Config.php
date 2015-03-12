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
 * This extension adds additional information to the module configuration.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_Module_Config extends mf_Module_Config_parent
{
    /**
     * Flag if the shop is verified at bepado.
     *
     * @var bool
     */
    private $isVerified = false;

    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * Simple constructor, to set a base value.
     */
    public function __construct()
    {
        $this->isVerified = null;
    }

    /**
     * The overwritten render method creates a little flag caused by the api key
     * validation.
     *
     * @return string
     */
    public function render()
    {
        $template = parent::render();
        if (!mf_module_helper::MODULE_ID !== $this->getEditObjectId()) {
            return $template;
        }

        $this->_aViewData['verified'] = $this->isVerified;

        return 'mf_module_config.tpl';
    }

    /**
     * We need to override this method to validate the api key before
     * persisting it.
     */
    public function saveConfVars()
    {
        parent::saveConfVars();

        if (mf_module_helper::MODULE_ID !== $this->getEditObjectId()) {
            return;
        }

        $this->isVerified = $this->getVersionLayer()
            ->createNewObject('mf_module_helper')
            ->onSaveConfigVars($this->_aConfParams);
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
