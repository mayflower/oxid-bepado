<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class SDKConfig
{
    /**
     * @var
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiEndpointUrl;

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
}
 