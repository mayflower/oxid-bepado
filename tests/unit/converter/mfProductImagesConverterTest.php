<?php

use Bepado\SDK\Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductImagesConverterTest extends BaseTestCase
{
    protected $mfSDKLoggerHelper;
    protected $mfSDKHelper;

    /**
     * @var mfProductImagesConverter
     */
    protected $converter;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->mfSDKHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->mfSDKLoggerHelper = $this->getMockBuilder('mf_sdk_logger_helper')->disableOriginalConstructor()->getMock();

        $this->converter = oxNew('mfProductImagesConverter');
        $this->converter->setVersionLayer($this->versionLayer);
    }

    public function testFromBepadoToShop()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Struct\Product();
        $oProduct->images = array('pic1');

        $this->mfSDKHelper
            ->expects($this->at(0))
            ->method('createOxidImageFromPath')
            ->with($this->equalTo('pic1'), $this->equalTo(1))
            ->will($this->returnValue(array('oxarticles__oxpic1', 'pic1')));
        $this->converter->fromBepadoToShop($oProduct, $oArticle);

        $this->assertEquals('pic1', $oArticle->getFieldData('oxarticles__oxpic1'));
    }

    public function testFromShopToBepado()
    {
        $oArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
        $oArticle
            ->expects($this->at(0))
            ->method('getFieldData')
            ->with($this->equalTo("oxarticles__oxpic1"))
            ->will($this->returnValue('pic1'));
        $oArticle->expects($this->at(1))->method('getPictureUrl')->will($this->returnValue('url1'));
        $oProduct = new Struct\Product();

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals(array('url1'), $oProduct->images);
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_helper'        => $this->mfSDKHelper,
            'mf_sdk_logger_helper' => $this->mfSDKLoggerHelper,
        );
    }
}
