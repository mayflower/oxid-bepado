<?php

/**
 * Base method for the different helper.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
abstract class mf_abstract_helper
{
    /**
     * @var VersionLayerInterface
     */
    protected $_oVersionLayer;

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    protected function getVersionLayer()
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
 