<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class SDKConfig
{
    const ARTICLE_STATE_EXPORTED = 1;

    const ARTICLE_STATE_IMPORTED = 2;

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
     * @return mixed
     */
    public function getApiEndpointUrl()
    {
        return $this->apiEndpointUrl;
    }

    /**
     * @param mixed $apiEndpointUrl
     */
    public function setApiEndpointUrl($apiEndpointUrl)
    {
        $this->apiEndpointUrl = $apiEndpointUrl;
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
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
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
     */
    public function setProdMode($urlHost)
    {
        $this->prodMode = $urlHost;
    }
}
 