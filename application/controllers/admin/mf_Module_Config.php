<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_Module_Config extends mf_Module_Config_parent
{
    private $_oModuleSdkHelper;

    public function render()
    {
        $template = parent::render();

        $sdkConfig = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($sdkConfig);
        $sdk->verifyKey($sdkConfig->getApiKey());

        return $template;
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
}
 