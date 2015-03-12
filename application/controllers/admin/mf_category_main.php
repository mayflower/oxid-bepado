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
 * This extension add additional information to the category main view.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_category_main extends mf_category_main_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * A category view (main) will get an additional select box
     * to map the bepado categories.
     *
     * @return string
     */
    public function render()
    {
        $aCategories = $this->getSdkCategories();

        $oxidCategoryId = parent::getEditObjectId();
        if (!isset($aCategories)) {
            $aCategories = [];
        }

        $bepadoCategory = $this->getVersionLayer()->createNewObject('oxbase');
        $bepadoCategory->init('bepado_categories');

        if ($oxidCategoryId != "-1" && isset($oxidCategoryId)){
            try {
                $query = $bepadoCategory->buildSelectString(array('catnid' => $oxidCategoryId));
                $bepadoCategoryId = $this->getVersionLayer()->getDb(true)->getOne($query);
                $bepadoCategory->load($bepadoCategoryId);
            } catch (\Exception $e) {
                // do nothing
            }
        }

        $this->_aViewData['googleCategories'] = $aCategories;
        $this->_aViewData['bepardoCategory'] = $bepadoCategory;

        return parent::render();
    }

    /**
     * On save there will be a check for a chosen category mapping
     * to persist this one in an additional database table.
     */
    public function save()
    {
        parent::save();
        $myConfig = parent::getConfig();
        $oxidCategoryId = parent::getEditObjectId();

        /** @var oxBase $bepadoCategory */
        $bepadoCategory = oxNew('oxbase');
        $bepadoCategory->init('bepado_categories');
        $query = $bepadoCategory->buildSelectString(array('catnid' => $oxidCategoryId));
        $bepadoCategoryId = $this->getVersionLayer()->getDb(true)->getOne($query);
        $bepadoCategory->load($bepadoCategoryId);

        // parameter for bepado category path
        $aParams = parent::_parseRequestParametersForSave($myConfig->getRequestParameter("mf_editval"));
        $googleCategoryPath = isset($aParams['bepado_categories__path']) && "" != $aParams['bepado_categories__path']
            ? $aParams['bepado_categories__path']
            : null;


        $googleCategories = $this->getSdkCategories();
        if (isset($googleCategories[$googleCategoryPath])) {
            $bepadoCategory->assign(array(
                'bepado_categories__catnid' => $oxidCategoryId,
                'bepado_categories__path' => $googleCategoryPath,
                'bepado_categories__title' => $googleCategories[$googleCategoryPath],
            ));
            $bepadoCategory->save();
        } else {
            if ($bepadoCategory->isLoaded()) {
                $bepadoCategory->delete();
            }
        }

        return;
    }

    /**
     * Serves a list of bepado categories.
     *
     * @return array
     */
    private function getSdkCategories()
    {
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $sdkConfig = $sdkHelper->createSdkConfigFromOxid();
        $sdk = $sdkHelper->instantiateSdk($sdkConfig);

        return $sdk->getCategories();
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
