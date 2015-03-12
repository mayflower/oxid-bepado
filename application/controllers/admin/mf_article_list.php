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
 * This extension add additional information to the article list.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_article_list extends mf_article_list_parent
{
    const EXPORT_PIC = 'application/out/img/bepado_out.png';

    const IMPORT_PIC = 'application/out/img/bepado_in.png';

    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * @var mf_sdk_article_helper
     */
    private $articleHelper;

    /**
     * Each article is checked for its import/export state.
     * Imported/exported articles will get an additional icon in the list.
     *
     * @return string
     */
    public function getItemList()
    {
        $oList = parent::getItemList();

        foreach ($this->_oList as $key => $listItem) {
            $state = $this->getArticleHelper()->getArticleBepadoState($listItem);

            if ($state == 1) {
                $listItem->oxarticles__state = new oxField(
                    self::EXPORT_PIC,
                    oxField::T_RAW
                );
            } elseif ($state == 2) {
                $listItem->oxarticles__state = new oxField(
                    self::IMPORT_PIC,
                    oxField::T_RAW
                );
            }
        }

        return $oList;
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
