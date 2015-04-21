<?php

use Bepado\SDK\Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductAttributesConverterTest extends BaseTestCase
{
    /**
     * @var mfProductAttributesConverter
     */
    protected $converter;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->converter = oxNew('mfProductAttributesConverter');
    }

    public function testFromBepadoToShop()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Struct\Product();
        $oProduct->availability = 10;
        $oProduct->attributes = array(
            Struct\Product::ATTRIBUTE_WEIGHT => 11,
            Struct\Product::ATTRIBUTE_VOLUME => '2184',
            Struct\Product::ATTRIBUTE_DIMENSION => '13x12x14',
            Struct\Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Struct\Product::ATTRIBUTE_QUANTITY => 10,
            Struct\Product::ATTRIBUTE_UNIT => 'g'
        );

        $this->converter->fromBepadoToShop($oProduct, $oArticle);

        $this->assertEquals('10', $oArticle->getFieldData('oxarticles__oxstock'));
        $this->assertEquals('11', $oArticle->getFieldData('oxarticles__oxweight'));
    }

    public function testFromShopToBepado()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Struct\Product();
        $oArticle->assign(array(
            'oxarticles__oxstock'  => '10',
            'oxarticles__oxweight' => '11'
        ));

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals(10, $oProduct->availability);
        $this->assertEquals(11, $oProduct->attributes[Struct\Product::ATTRIBUTE_WEIGHT]);
    }

    protected function getObjectMapping()
    {
        return array();
    }
}
