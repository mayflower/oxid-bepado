<?php

class mfBepado extends oxUbase
{
    /**
     * @var mf_sdk_helper
     */
    private $_oModuleSdkHelper;

    public function render() {
        parent::render();

        /** @var mf_sdk_logger_helper $logger */
        $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');

        $sdkConfig = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($sdkConfig);
        try {
            echo $sdk->handle(file_get_contents('php://input'), $_SERVER);
        } catch (\Exception $e) {
            $logger->writeBepadoLog($e->getMessage());
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

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    private function getVersionLayer()
    {
        /** @var VersionLayerFactory $factory */
        $factory = oxNew('VersionLayerFactory');
        $oVersionLayer = $factory->create();

        return $oVersionLayer;
    }
}
