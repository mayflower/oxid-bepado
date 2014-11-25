<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class BaseTestCase extends PHPUnit_Framework_TestCase
{

    /**
     * @var VersionLayerInterface
     */
    protected $versionLayer;

    /**
     * @var oxConfig
     */
    protected $oxidConfig;

    protected function prepareVersionLayerWithConfig()
    {
        $this->versionLayer = $this->getMock('VersionLayerInterface');
        $this->oxidConfig = $this->getMock('oxConfig');
        $this->versionLayer->expects($this->any())->method('getConfig')->will($this->returnValue($this->oxidConfig));

        $this->oxidConfig->expects($this->any())
            ->method('getConfigParam')
            ->will($this->returnValue('test-value'));
    }
}
 