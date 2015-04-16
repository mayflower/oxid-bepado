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
 * This controller will render the main view of the imported products admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_import_main extends oxAdminDetails
{
    /**
     * Unit array
     *
     * @var array
     */
    protected $_aUnitsArray = null;

    /**
     * @var VersionLayerInterface
     */
    protected $_oVersionLayer;

    public function render()
    {
        parent::render();

        $oBepadoProduct = $this->getVersionLayer()->createNewObject('mfBepadoProduct');
        $this->_aViewData['edit'] = new \stdClass();
        $this->_aViewData['edit']->mfBepadoProduct = $oBepadoProduct;

        $oxId = $this->getEditObjectId();
        if ($oxId && '-1' !== $oxId) {
            $oBepadoProduct->load($oxId);
        }

        $oArticle = $this->getVersionLayer()->createNewObject('oxArticle');
        $oArticle->load($oBepadoProduct->mfbepadoproducts__oxid->value);
        $this->_aViewData['edit']->oxArticle = $oArticle;
        $this->_aViewData["editor"] = $this->_generateTextEditor(
            "100%",
            300,
            $oArticle,
            "oxarticles__oxlongdesc",
            "details.tpl.css"
        );
        $this->_aViewData['edit']->oArticleHelper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');

        return 'mf_product_import_main.tpl';
    }

    /**
     * When saving a bepado product model, we will save the allowed values of the oxid article representation.
     */
    public function save()
    {
        parent::save();

        $aParams = $this->getVersionLayer()->getConfig()->getRequestParameter("editval");

        $oArticle = $this->getVersionLayer()->createNewObject('oxArticle');
        /** @var oxArticle $oArticle the id of the bepado product model and the oxid article representation is the same */
        $oArticle->load($this->getEditObjectId());
        $oArticle->assign($aParams['oxArticle']);
        $oArticle->setArticleLongDesc($aParams['oxarticles__oxlongdesc']);

        $oArticle->save();
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
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
     * Returns string which must be edited by editor
     *
     * @param mfBepadoProduct $oObject object whifh field will be used for editing
     * @param string $sField  name of editable field
     *
     * @return string
     */
    protected function _getEditValue($oObject, $sField)
    {
        $sEditObjectValue = '';
        if ($oObject) {
            $oDescField = $oObject->getLongDescription();
            $sEditObjectValue = $this->_processEditValue($oDescField->getRawValue());
        }

        return $sEditObjectValue;
    }

    /**
     * Returns shop manufacturers list
     *
     * @return oxmanufacturerlist
     */
    public function getVendorList()
    {
        $oVendorlist = $this->getVersionLayer()->createNewObject('oxVendorList');
        $oVendorlist->loadVendorList();

        return $oVendorlist;
    }

    /**
     * Returns array of possible unit combination and its translation for edit language
     *
     * @return array
     */
    public function getUnitsArray()
    {
        if ($this->_aUnitsArray === null) {
            $this->_aUnitsArray = oxRegistry::getLang()->getSimilarByKey("_UNIT_", $this->_iEditLang, false);
        }

        return $this->_aUnitsArray;
    }
}
