<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class SDKConfig
{
    const ARTICLE_STATE_NONE = 0;

    const ARTICLE_STATE_EXPORTED = 1;

    const ARTICLE_STATE_IMPORTED = 2;

    const SHOP_ID_LOCAL = '_self_';

    const SEARCH_HOST_DEMO = 's.server1230-han.de-nserver.de';
    const TRANSACTION_HOST_DEMO = 't.server1230-han.de-nserver.de';
    const SOCIALNETWORK_HOST_DEMO = 'sn.server1230-han.de-nserver.de';

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
    private $prodMode;

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
    public function getProdMode()
    {
        return $this->prodMode;
    }

    /**
     * @param string $urlHost
     *
     * @return SDKConfig
     */
    public function setProdMode($urlHost)
    {
        $this->prodMode = $urlHost;

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
