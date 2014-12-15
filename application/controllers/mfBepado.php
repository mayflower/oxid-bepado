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
        $helper->handleRequest();

        return $this->_sThisTemplate;
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
}
