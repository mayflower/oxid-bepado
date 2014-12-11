<?php

/**
 * Abstraction for all converter classes.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
abstract class mf_abstract_converter
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
