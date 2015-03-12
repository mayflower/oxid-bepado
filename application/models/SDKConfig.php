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
use Bepado\SDK\Struct\Order;

/**
 * A model for sdk configuration values to instantiate the sdk.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class SDKConfig
{
    const ARTICLE_STATE_NONE = 0;
    const ARTICLE_STATE_EXPORTED = 1;
    const ARTICLE_STATE_IMPORTED = 2;
    const SHOP_ID_LOCAL = '_self_';
    const SEARCH_HOST_DEMO = 'search.server1230-han.de-nserver.de';
    const TRANSACTION_HOST_DEMO = 'transaction.server1230-han.de-nserver.de';
    const SOCIALNETWORK_HOST_DEMO = 'sn.server1230-han.de-nserver.de';
    const DEFAULT_PAYMENT_METHOD = Order::PAYMENT_INVOICE;

    /**
     * @var
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiEndpointUrl;

    /**
     * @var string
     */
    private $sandboxMode;

    /**
     * @var string
     */
    private $socialnetworkHost;

    /**
     * @var string
     */
    private $transactionHost;

    /**
     * @var string
     */
    private $searchHost;

    /**
     * @return mixed
     */
    public function getApiEndpointUrl()
    {
        return $this->apiEndpointUrl;
    }

    /**
     * @param mixed $apiEndpointUrl
     *
     * @return SDKConfig
     */
    public function setApiEndpointUrl($apiEndpointUrl)
    {
        $this->apiEndpointUrl = $apiEndpointUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param mixed $apiKey
     *
     * @return SDKConfig
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getSandboxMode()
    {
        return $this->sandboxMode;
    }

    /**
     * @param string $sandboxMode
     *
     * @return SDKConfig
     */
    public function setSandboxMode($sandboxMode)
    {
        $this->sandboxMode = $sandboxMode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSearchHost()
    {
        return $this->searchHost;
    }

    /**
     * @param string $searchHost
     *
     * @return SDKConfig
     */
    public function setSearchHost($searchHost)
    {
        $this->searchHost = $searchHost;

        return $this;
    }

    /**
     * @return string
     */
    public function getSocialnetworkHost()
    {
        return $this->socialnetworkHost;
    }

    /**
     * @param string $socialnetworkHost
     *
     * @return SDKConfig
     */
    public function setSocialnetworkHost($socialnetworkHost)
    {
        $this->socialnetworkHost = $socialnetworkHost;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionHost()
    {
        return $this->transactionHost;
    }

    /**
     * @param string $transactionHost
     *
     * @return SDKConfig
     */
    public function setTransactionHost($transactionHost)
    {
        $this->transactionHost = $transactionHost;

        return $this;
    }
}
