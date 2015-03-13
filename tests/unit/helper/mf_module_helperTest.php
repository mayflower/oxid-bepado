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
        $sdkConfig = new mfBepadoConfiguration();
        $sdkConfig
            ->setApiEndpointUrl('some-url')
            ->setApiKey('some-key')
            ->setSocialnetworkHost('some-host')
            ->setSandboxMode(true)
        ;
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->sdkHelper->expects($this->any())->method('createSdkConfigFromOxid')->will($this->returnValue($sdkConfig));
        $this->sdkHelper->expects($this->any())->method('instantiateSdk')->will($this->returnValue($this->sdk));
    }

    public function testSaveOnConfVarsVerified()
    {
        $this->createmfBepadoConfiguration();
        $this->sdk->expects($this->once())->method('verifyKey');

        $result = $this->helper->onSaveConfigVars($this->configVars);

        $this->assertTrue($result);
    }

    public function testOnSaveConfVarsNotVerified()
    {
        $this->createmfBepadoConfiguration();

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

    private function createmfBepadoConfiguration()
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

    public function testNetPriceCalculationShopIsNetNetGoesIn()
    {
        $versionLayer = $this->getMock('VersionLayerInterface');
        $oxidConfig = $this->getMock('oxConfig');
        $versionLayer->expects($this->any())->method('getConfig')->will($this->returnValue($oxidConfig));
        $oxidConfig->expects($this->any())
            ->method('getConfigParam')
            ->will($this->returnValue(true));
        $this->helper->setVersionLayer($versionLayer);

        /** @var oxPrice $price */
        $price = oxNew('oxPrice');
        $price->setNettoPriceMode();
        $price->setVat(19);
        $price->setPrice(2.00);


        $actualPrice = $this->helper->createNetPrice($price);
        $this->assertEquals(2.00, $actualPrice);
    }

    public function testNetPriceCalculationShopIsBrutBrutGoesIn()
    {
        $versionLayer = $this->getMock('VersionLayerInterface');
        $oxidConfig = $this->getMock('oxConfig');
        $versionLayer->expects($this->any())->method('getConfig')->will($this->returnValue($oxidConfig));
        $oxidConfig->expects($this->any())
            ->method('getConfigParam')
            ->will($this->returnValue(false));
        $this->helper->setVersionLayer($versionLayer);

        /** @var oxPrice $price */
        $price = oxNew('oxPrice');
        $price->setBruttoPriceMode();
        $price->setVat(19);
        $price->setPrice(2.00);


        $actualPrice = $this->helper->createNetPrice($price);
        $this->assertEquals(1.680672268907563, $actualPrice);
    }

    public function testNetPriceCalculationShopIsNetBrutGoesIn()
    {
        $versionLayer = $this->getMock('VersionLayerInterface');
        $oxidConfig = $this->getMock('oxConfig');
        $versionLayer->expects($this->any())->method('getConfig')->will($this->returnValue($oxidConfig));
        $oxidConfig->expects($this->any())
            ->method('getConfigParam')
            ->will($this->returnValue(true));
        $this->helper->setVersionLayer($versionLayer);

        /** @var oxPrice $price */
        $price = oxNew('oxPrice');
        $price->setBruttoPriceMode();
        $price->setVat(19);
        $price->setPrice(2.00);


        $actualPrice = $this->helper->createNetPrice($price);
        $this->assertEquals(2.00, $actualPrice);
    }

    public function testNetPriceCalculationShopIsBrutNetGoesIn()
    {
        $versionLayer = $this->getMock('VersionLayerInterface');
        $oxidConfig = $this->getMock('oxConfig');
        $versionLayer->expects($this->any())->method('getConfig')->will($this->returnValue($oxidConfig));
        $oxidConfig->expects($this->any())
            ->method('getConfigParam')
            ->will($this->returnValue(false));
        $this->helper->setVersionLayer($versionLayer);

        /** @var oxPrice $price */
        $price = oxNew('oxPrice');
        $price->setNettoPriceMode();
        $price->setVat(19);
        $price->setPrice(2.00);


        $actualPrice = $this->helper->createNetPrice($price);
        $this->assertEquals(1.680672268907563, $actualPrice);
    }
}
