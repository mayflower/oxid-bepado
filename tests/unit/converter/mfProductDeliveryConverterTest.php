<?php

use Bepado\SDK\Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductDeliveryConverterTest extends BaseTestCase
{
    /**
     * @var mfProductDeliveryConverter
     */
    protected $converter;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->converter = oxNew('mfProductDeliveryConverter');
    }

    public function testFromBepadoToShop()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Struct\Product();
        $oProduct->deliveryDate = 2424121200;
        $oProduct->deliveryWorkDays = 10;
        $this->converter->fromBepadoToShop($oProduct, $oArticle);

        $this->assertEquals('2046-10-26', $oArticle->getFieldData('oxarticles__oxdelivery'));
        $this->assertEquals('10', $oArticle->getFieldData('oxarticles__oxmaxdeltime'));
        $this->assertEquals('DAY', $oArticle->getFieldData('oxarticles__oxdeltimeunit'));
    }

    public function testFromShopToBepado()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Struct\Product();
        $oArticle->assign(array(
            'oxarticles__oxdelivery'     => '2046-10-26',
            'oxarticles__oxmaxdeltime'   => '2',
            'oxarticles__oxdeltimeunit'  => 'WEEK'
        ));

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals(2424121200, $oProduct->deliveryDate);
        $this->assertEquals(10, $oProduct->deliveryWorkDays);
    }

    protected function getObjectMapping()
    {
        return array();
    }
}
