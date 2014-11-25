<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_helperTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var mf_sdk_helper
     */
    private $helper;

    /**
     * @var VersionLayerInterface
     */
    private $versionLayer;

    /**
     * @var oxConfig
     */
    private $oxidConfig;

    public function setUp()
    {
        $this->versionLayer = $this->getMock('VersionLayerInterface');
        $this->helper = new mf_sdk_helper();
        $this->helper->setVersionHelper($this->versionLayer);
        $this->oxidConfig = $this->getMock('oxConfig');
        $this->versionLayer->expects($this->any())->method('getConfig')->will($this->returnValue($this->oxidConfig));

    }
    public function testConfigCreation()
    {
        $this->oxidConfig->expects($this->at(0))
            ->method('getConfigParam')
            ->with($this->equalTo('sBepadoLocalEndpoint'))
            ->will($this->returnValue('test-endpoint'));
        $this->oxidConfig->expects($this->at(1))
            ->method('getConfigParam')
            ->with($this->equalTo('sBepadoApiKey'))
            ->will($this->returnValue('test-key'));

        $this->oxidConfig->expects($this->at(2))
            ->method('getConfigParam')
            ->with($this->equalTo('prodMode'))
            ->will($this->returnValue(false));
        $sdConfig = $this->helper->createSdkConfigFromOxid();

        $this->assertEquals('test-endpoint', $sdConfig->getApiEndpointUrl());
        $this->assertEquals('test-key', $sdConfig->getApiKey());
        $this->assertFalse($sdConfig->getProdMode());
    }
}
 