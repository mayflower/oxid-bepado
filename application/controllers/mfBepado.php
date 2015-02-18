<?php

class mfBepado extends oxUbase
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    public function render() {
        parent::render();

        /** @var mf_sdk_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $this->_aViewData['sdk_result'] = $helper->handleRequest();

        return 'mf_sdk_result.tpl';
    }

    private function getVersionLayer()
    {
        if (null == $this->_oVersionLayer) {
            /** @var VersionLayerFactory $factory */
            $factory = oxNew('VersionLayerFactory');
            $this->_oVersionLayer = $factory->create();
        }

        return $this->_oVersionLayer;
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }
}
