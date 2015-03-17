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
 * A model for sdk configuration values to instantiate the sdk.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mfBepadoConfiguration extends oxBase
{
    const ARTICLE_STATE_NONE = 0;
    const ARTICLE_STATE_EXPORTED = 1;
    const ARTICLE_STATE_IMPORTED = 2;
    const SHOP_ID_LOCAL = '_self_';
    const SEARCH_HOST_DEMO = 'search.server1230-han.de-nserver.de';
    const TRANSACTION_HOST_DEMO = 'transaction.server1230-han.de-nserver.de';
    const SOCIALNETWORK_HOST_DEMO = 'sn.server1230-han.de-nserver.de';
    const DEFAULT_PAYMENT_TYPE = 'bepadopaymenttype';
    const DATABASE_BASE_STRING = 'mfbepadoconfiguration__';
    const API_ENDPOINT_URL_SUFFIX = 'index.php?cl=mfbepado';

    /**
     * @var string The current endpoint url based on the shop and its url.
     */
    protected $apiEndpointUrl;

    public function __construct()
    {
        parent::init('mfbepadoconfiguration');
    }

    /**
     * Will return the current api endpoint url.
     *
     * @return mixed
     */
    public function getApiEndpointUrl()
    {
        return $this->apiEndpointUrl;
    }

    /**
     * Will set the endpoint url to the configuration.
     *
     * @param mixed $apiEndpointUrl
     *
     * @return mfBepadoConfiguration
     */
    public function setApiEndpointUrl($apiEndpointUrl)
    {
        $this->apiEndpointUrl = $apiEndpointUrl;

        return $this;
    }

    /**
     * Will return the current api key.
     *
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'apikey');
    }

    /**
     * Will set the api key to the configuration.
     *
     * @param mixed $apiKey
     *
     * @return mfBepadoConfiguration
     */
    public function setApiKey($apiKey)
    {
        $this->_setFieldData(self::DATABASE_BASE_STRING.'apikey', $apiKey);

        return $this;
    }

    /**
     * Will return the current sandbox mode.
     *
     * @return string
     */
    public function getSandboxMode()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'sandboxmode');
    }

    /**
     * Will set the sandbox mode to the configuration.
     *
     * @param string $sandboxMode
     *
     * @return mfBepadoConfiguration
     */
    public function setSandboxMode($sandboxMode)
    {
        $this->_setFieldData(self::DATABASE_BASE_STRING.'sandboxmode', $sandboxMode);

        return $this;
    }

    /**
     * Gets the flag whether is Remote shop should be visible on
     * the article details page or not.
     *
     * @return mixed
     */
    public function getShopHintOnArticleDetails()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'marketplacehintarticle');
    }

    /**
     * Set the flag whether is Remote shop should be visible on
     * the article details page or not.
     *
     * @param bool $shopHint
     *
     * @return $this
     */
    public function setShopHintOnArticleDetails($shopHint)
    {
        $this->_setFieldData(self::DATABASE_BASE_STRING.'marketplacehintarticle', $shopHint);

        return $this;
    }

    /**
     * Gets the flag whether is Remote shop should be visible in
     * the basket page or not.
     *
     * @return mixed
     */
    public function getShopHintInBasket()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'marketplacehintbasket');
    }

    /**
     * Set the flag whether is Remote shop should be visible in
     * the basket page or not.
     *
     * @param bool $shopHint
     *
     * @return $this
     */
    public function setShopHintInBasket($shopHint)
    {
        $this->_setFieldData(self::DATABASE_BASE_STRING.'marketplacehintbasket', $shopHint);

        return $this;
    }

    /**
     * @return bool
     */
    public function hastShopHintInBasket()
    {
        return '1' === $this->getShopHintInBasket();
    }

    /**
     * @return bool
     */
    public function hastShopHintOnArticleDetails()
    {
        return '1' === $this->getShopHintOnArticleDetails();
    }

    /**
     * @return bool
     */
    public function isInSandboxMode()
    {
        return '1' === $this->getSandboxMode();
    }


    public function setPurchaseGroup($purchaseGroupCharakter)
    {
        $this->_setFieldData(self::DATABASE_BASE_STRING.'purchasegroup', $purchaseGroupCharakter);

        return $this;
    }

    public function getPurchaseGroup()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'purchasegroup');
    }

    /**
     * Returns the demo search host, when in sandbox mode.
     *
     * @return string|null
     */
    public function getSearchHost()
    {
        return $this->isInSandboxMode() ? self::SEARCH_HOST_DEMO : null;
    }

    /**
     * Returns the demo socialnetwork host, when in sandbox mode.
     *
     * @return string|null
     */
    public function getSocialnetworkHost()
    {
        return $this->isInSandboxMode() ? self::SOCIALNETWORK_HOST_DEMO : null;
    }

    /**
     * Returns the demo transaction host, when in sandbox mode.
     *
     * @return string|null
     */
    public function getTransactionHost()
    {
        return $this->isInSandboxMode() ? self::TRANSACTION_HOST_DEMO : null;
    }
}
