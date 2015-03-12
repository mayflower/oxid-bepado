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
 * Version layer of version 4.6.*.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 *
 * @codeCoverageIgnore
 */
class VersionLayer460 implements VersionLayerInterface
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
        return oxSession::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getConfig()
    {
        return oxConfig::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getDb($bAssoc = false)
    {
        return oxDb::getDb($bAssoc);
    }

    /**
     * {@inheritDocs}
     */
    public function getDeliverySetList()
    {
        return oxDeliverySetList::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtils()
    {
        return oxUtils::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getRequestParam($sName, $mDefaultValue = null, $blRaw = false)
    {
        $mReturnValue = oxConfig::getParameter($sName, $blRaw);

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
        return oxLang::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsServer()
    {
        return oxUtilsServer::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsUrl()
    {
        return oxUtilsUrl::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsView()
    {
        return oxUtilsView::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsObject()
    {
        return oxUtilsObject::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsDate()
    {
        return oxUtilsDate::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsString()
    {
        return oxUtilsString::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsFile()
    {
        return oxUtilsFile::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsPic()
    {
        return oxUtilsPic::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function getUtilsCount()
    {
        return oxUtilsCount::getInstance();
    }

    /**
     * {@inheritDocs}
     */
    public function createNewObject($className)
    {
        return oxNew($className);
    }
}

