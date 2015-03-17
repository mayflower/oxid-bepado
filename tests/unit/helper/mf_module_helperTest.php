<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_module_helperTest extends BaseTestCase
{
    /**
     * @var mf_module_helper
     */
    protected $helper;

    protected $sdkHelper;
    protected $sdk;
    protected $bepadoConfiguration;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->helper = new mf_module_helper();
        $this->helper->setVersionLayer($this->versionLayer);
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->sdkHelper->expects($this->any())->method('instantiateSdk')->will($this->returnValue($this->sdk));
        $this->bepadoConfiguration = new mfBepadoConfiguration();
        $this->sdkHelper
            ->expects($this->any())
            ->method('computeConfiguration')
            ->will($this->returnValue($this->bepadoConfiguration));
        $this->bepadoConfiguration->assign(array(
            'mfbepadoconfiguration__oxid' => 'shop-id',
            'mfbepadoconfiguration__sandboxmode' => '1',
            'mfbepadoconfiguration__shophintonarticledetails' => '0',
            'mfbepadoconfiguration__marketplacehintbasket' => '0',
            'mfbepadoconfiguration__apikey' => 'some-key',
        ));
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_helper'         => $this->sdkHelper,
            'mfBepadoConfiguration' => $this->bepadoConfiguration,
        );
    }

    public function testThruthyVerifyKey()
    {
        $this->sdk
            ->expects($this->once())
            ->method('verifyKey')
            ->with($this->equalTo('some-key'));

        $result = $this->helper->verifyAtSdk($this->bepadoConfiguration);

        $this->assertTrue($result);
    }

    public function testFalsyVerifyKey()
    {
        $this->sdk
            ->expects($this->once())
            ->method('verifyKey')
            ->with($this->equalTo('some-key'))
            ->will($this->returnCallback(function() {
                throw new \RuntimeException('some text');
            }));
        ;
        $result = $this->helper->verifyAtSdk($this->bepadoConfiguration);

        $this->assertFalse($result);
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
