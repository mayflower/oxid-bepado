<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_Module_Config extends mf_Module_Config_parent
{
    const API_KEY_SETTING_NAME = 'sBepadoApiKey';

    const MODULE_ID = 'bepado';

    const API_URL_SETTING_NAME = 'sBepadoLocalEndpoint';

    private $_oModuleSdkHelper;

    private $isVerified;

    public function __construct()
    {
        $this->isVerified = null;
    }

    /**
     * The overwritten render method creates a little flag caused by the api key
     * validation.
     *
     * @return string
     */
    public function render()
    {
        $template = parent::render();
        if (!$this->isBepadoModule()) {
            return $template;
        }

        $this->_aViewData['verified'] = $this->isVerified;

        return 'mf_module_config.tpl';
    }

    /**
     * We need to override this method to validate the api key before
     * persisting it.
     */
    public function saveConfVars()
    {
        parent::saveConfVars();
        if (!$this->isBepadoModule()) {
            return;
        }

        $oConfig = $this->getConfig();
        $sdkConfig = $this->getSdkHelper()->createSdkConfigFromOxid();

        foreach ($this->_aConfParams as $sType => $sParam) {
            $aConfVars = $oConfig->getRequestParameter($sParam);
            if (is_array($aConfVars)) {
                foreach ($aConfVars as $sName => $sValue) {
                    if (self::API_KEY_SETTING_NAME === $sName) {
                        $sdkConfig->setApiKey($sValue);
                    } elseif (self::API_URL_SETTING_NAME === $sValue) {
                        $sdkConfig->setApiEndpointUrl($sValue);
                    }
                }
            }
        }

        if ($this->verifyAtSdk($sdkConfig)) {
            $this->isVerified = true;
        } else {
            $this->isVerified = false;
        }
    }

    /**
     * @param SDKConfig $sdkConfig
     *
     * @return bool
     */
    private function verifyAtSdk(SDKConfig $sdkConfig)
    {
        $sdk = $this->getSdkHelper()->instantiateSdk($sdkConfig);

        try {
            $sdk->verifyKey($sdkConfig->getApiKey());
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * @return mf_sdk_helper
     */
    private function getSdkHelper()
    {
        if ($this->_oModuleSdkHelper === null) {
            $this->_oModuleSdkHelper = oxNew('mf_sdk_helper');
        }

        return $this->_oModuleSdkHelper;
    }

    /**
     * As we need to implement the features for the bepado module configuration settings only.
     * @return bool
     */
    private function isBepadoModule()
    {
        return self::MODULE_ID === $this->getEditObjectId();
    }
}
 