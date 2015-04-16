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
        $oxArticle = oxNew('oxarticle');
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
     */
    public function save()
    {
        $this->getArticleHelper()->onSaveArticleExtend($this->getEditObjectId());

        parent::save();
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
}
