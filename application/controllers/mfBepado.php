<?php

class mfBepado extends oxUbase
{
    /**
     * @var mf_sdk_helper
     */
    private $_oModuleSdkHelper;

    public function render() {
        parent::render();

        $sdkConfig = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($sdkConfig);
        try {
            echo $sdk->handle(file_get_contents('php://input'), $_SERVER);
        } catch (\Exception $e) {
            // todo do something
        }

        return $this->_sThisTemplate;
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
