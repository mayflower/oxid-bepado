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

use Bepado\SDK\Exception\VerificationFailedException;

/**
 * This extension adds additional behavior/information to the article admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_article_extend extends mf_article_extend_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * @var mf_sdk_article_helper
     */
    private $articleHelper;

    /**
     * Add information of the export/import state to the view model.
     *
     * @return string
     */
    public function render()
    {
        $template = parent::render();

        /** @var oxArticle $oxArticle */
        $oxArticle = $this->getVersionLayer()->createNewObject('oxArticle');
        $oxArticle->load($this->getEditObjectId());
        $state = $this->getArticleHelper()->getArticleBepadoState($oxArticle);

        if ($state != 2) {
            $this->_aViewData['export_to_bepado'] = $state;
            $this->_aViewData['no_bepado_import'] = 1;
        }

        return $template;
    }

    /**
     * Triggers a hook for work on saving articles.
     *
     * When an article is exported, it might be not valid when being converted into a sdk product, so we will
     * rollback and give an error message.
     */
    public function save()
    {
        $this->getArticleHelper()->onSaveArticleExtend($this->getEditObjectId());

        try {
            parent::save();
            $this->_aViewData['updatelist'] = true;
        } catch (VerificationFailedException $e) {
            $this->_aViewData['errorExportingArticle'] = true;
            $this->_aViewData['errorMessage'] = $e->getMessage();
            $this->getArticleHelper()->rollbackArticleExport($this->getEditObjectId());
        }
    }

    /**
     * @return mf_sdk_article_helper
     */
    private function getArticleHelper()
    {
        if (null === $this->articleHelper) {
            $this->articleHelper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');
        }

        return $this->articleHelper;
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
