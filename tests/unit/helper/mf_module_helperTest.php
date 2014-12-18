<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_module_helperTest extends BaseTestCase
{

    protected $sdkHelper;

    /**
     * @var mf_module_helper
     */
    protected $helper;

    protected $sdk;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->helper = new mf_module_helper();
        $this->helper->setVersionLayer($this->versionLayer);
        $sdkConfig = new SDKConfig();
        $sdkConfig
            ->setApiEndpointUrl('some-url')
            ->setApiKey('some-key')
            ->setSocialnetworkHost('some-host')
            ->setProdMode(true)
        ;
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->sdkHelper->expects($this->any())->method('createSdkConfigFromOxid')->will($this->returnValue($sdkConfig));
        $this->sdkHelper->expects($this->any())->method('instantiateSdk')->will($this->returnValue($this->sdk));
    }

    public function testSaveOnConfVarsVerified()
    {
        $this->createSDKConfig();
        $this->sdk->expects($this->once())->method('verifyKey');

        $result = $this->helper->onSaveConfigVars($this->configVars);

        $this->assertTrue($result);
    }

    public function testOnSaveConfVarsNotVerified()
    {
        $this->createSDKConfig();

        $exception = new \RuntimeException();
        $this->sdk->expects($this->once())->method('verifyKey')->will($this->throwException($exception));

        $result = $this->helper->onSaveConfigVars($this->configVars);

        $this->assertFalse($result);
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_helper' => $this->sdkHelper,
        );
    }

    private function createSDKConfig()
    {
        $this->oxidConfig
            ->expects($this->at(0))
            ->method('getRequestParameter')
            ->with($this->equalTo('confbools'))
            ->will($this->returnValue(array('prodMode' => 'false')));
        $this->oxidConfig
            ->expects($this->at(1))
            ->method('getRequestParameter')
            ->with($this->equalTo('confstr'))
            ->will($this->returnValue(array(
                'sBepadoLocalEndpoint' => 'test-url',
                'sBepadoApiKey'        => 'test-key'
            )));
        $this->oxidConfig
            ->expects($this->at(4))
            ->method('getRequestParameter')
            ->with($this->equalTo('confselects'))
            ->will($this->returnValue(array(
                'sPurchaseGroupChar' => 'A',
            )));
    }
}
