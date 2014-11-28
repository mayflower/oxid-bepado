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
        $this->assertNotNull($sdConfig->getSocialnetworkHost());
        $this->assertNotNull($sdConfig->getTransactionHost());
        $this->assertNotNull($sdConfig->getSearchHost());
    }

    public function testConfigCreationInProductMode()
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
            ->will($this->returnValue(true));
        $sdConfig = $this->helper->createSdkConfigFromOxid();

        $this->assertEquals('test-endpoint', $sdConfig->getApiEndpointUrl());
        $this->assertEquals('test-key', $sdConfig->getApiKey());
        $this->assertTrue($sdConfig->getProdMode());
        $this->assertNull($sdConfig->getSocialnetworkHost());
        $this->assertNull($sdConfig->getTransactionHost());
        $this->assertNull($sdConfig->getSearchHost());
    }

    public function testImageCreation()
    {
        $this->oxidConfig
            ->expects($this->any())
            ->method('getMasterPictureDir')
            ->will($this->returnValue(__DIR__.'/../testdata/'));
        list($fieldName, $fieldValue) = $this->helper
            ->createOxidImageFromPath('https://media-cdn.tripadvisor.com/media/photo-s/05/03/17/b3/aviarios-del-caribe-sloth.jpg', 1);

        $this->assertEquals('oxarticles__oxpic1', $fieldName);
        $this->assertEquals('aviarios-del-caribe-sloth.jpg', $fieldValue);

        // clean up
        $filePath = __DIR__.'/../testdata/product/1/aviarios-del-caribe-sloth.jpg';
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * @expectedException \Exception
     */
    public function testImageCreateWithNonExistingFile()
    {
        $this->helper->createOxidImageFromPath('some-path', 1);
    }

    protected function getObjectMapping()
    {
        return array();
    }
}
 