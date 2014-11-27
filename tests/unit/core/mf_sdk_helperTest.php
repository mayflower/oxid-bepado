<?php

require_once __DIR__.'/../BaseTestCase.php';

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_helperTest extends BaseTestCase
{
    /**
     * @var mf_sdk_helper
     */
    private $helper;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->helper = new mf_sdk_helper();
        $this->helper->setVersionLayer($this->versionLayer);
    }

    /**
     * As i wanna check the real values delivered by the config, we need to do an extra action
     * instead of the the default value of the base test case.
     */
    public function testConfigCreation()
    {
        $oxConfig = $this->getMock('oxConfig');
        $versionLayer = $this->getMock('VersionLayerInterface');
        $versionLayer->expects($this->once())->method('getConfig')->will($this->returnValue($oxConfig));
        $this->helper->setVersionLayer($versionLayer);
        $oxConfig->expects($this->at(0))
            ->method('getConfigParam')
            ->with($this->equalTo('sBepadoLocalEndpoint'))
            ->will($this->returnValue('test-endpoint'));
        $oxConfig->expects($this->at(1))
            ->method('getConfigParam')
            ->with($this->equalTo('sBepadoApiKey'))
            ->will($this->returnValue('test-key'));

        $oxConfig->expects($this->at(2))
            ->method('getConfigParam')
            ->with($this->equalTo('prodMode'))
            ->will($this->returnValue(false));
        $sdConfig = $this->helper->createSdkConfigFromOxid();

        $this->assertEquals('test-endpoint', $sdConfig->getApiEndpointUrl());
        $this->assertEquals('test-key', $sdConfig->getApiKey());
        $this->assertFalse($sdConfig->getProdMode());
    }

    protected function getObjectMapping()
    {
        return array();
    }
}
 