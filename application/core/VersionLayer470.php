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

// todo Remove that, just needed for test, as the don't respect the oxid autoloader.
require_once __DIR__ . '/interface/VersionLayerInterface.php';

/**
 * Version layer for Version 4.7.*
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class VersionLayer470 implements VersionLayerInterface
{
    /**
     * {@inheritDocs}
     */
    public function getBasket()
    {
        return $this->getSession()->getBasket();
    }

    /**
     * {@inheritDocs}
     */
    public function getSession()
    {
        return oxRegistry::getSession();
    }

    /**
     * {@inheritDocs}
     */
    public function getConfig()
    {
        return oxRegistry::getConfig();
    }

    /**
     * {@inheritDocs}
     */
    public function getDb($bAssoc = false)
    {
        if ($bAssoc) {
            $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        } else {
            $oDb = oxDb::getDb(oxDb::FETCH_MODE_NUM);
        }
        return $oDb;
    }

    /**
     * {@inheritDocs}
     */
    public function getDeliverySetList()
    {
        return oxRegistry::get('oxDeliverySetList');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtils()
    {
        return oxRegistry::getUtils();
    }

    /**
     * {@inheritDocs}
     */
    public function getRequestParam($sName, $mDefaultValue = null, $blRaw = false)
    {
        $oConfig      = $this->getConfig();
        $mReturnValue = $oConfig->getRequestParameter($sName, $blRaw);

        if ($mReturnValue === null) {
            $mReturnValue = $mDefaultValue;
        }

        return $mReturnValue;
    }

    /**
     * {@inheritDocs}
     */
    public function getLang()
    {
        return oxRegistry::getLang();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsServer()
    {
        return oxRegistry::get('oxUtilsServer');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsUrl()
    {
        return oxRegistry::get('oxUtilsUrl');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsView()
    {
        return oxRegistry::get('oxUtilsView');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsObject()
    {
        return oxRegistry::get('oxUtilsObject');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsDate()
    {
        return oxRegistry::get('oxUtilsDate');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsString()
    {
        return oxRegistry::get('oxUtilsString');
    }

    /**
     * {@inheritDocs}*
     */
    public function getUtilsFile()
    {
        return oxRegistry::get('oxUtilsFile');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsPic()
    {
        return oxRegistry::get('oxUtilsPic');
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsCount()
    {
        return oxRegistry::get('oxUtilsCount');
    }

    /**
     * {@inheritDocs}
     */
    public function createNewObject($className)
    {
        return oxNew($className);
    }
}
