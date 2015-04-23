<?php
use Bepado\SDK\Struct\Product;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductPricingConverterTest extends BaseTestCase
{
    /**
     * @var mfProductPricingConverter
     */
    protected $converter;
    protected $moduleHelper;

    /**
     * @var mfBepadoConfiguration
     */
    protected $mfBepadoConfiguration;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->moduleHelper = $this->getMockBuilder('mf_module_helper')->disableOriginalConstructor()->getMock();
        $this->mfBepadoConfiguration = oxNew('mfBepadoConfiguration');

        $this->converter = oxNew('mfProductPricingConverter');
        $this->converter->setVersionLayer($this->versionLayer);

        $oCur = new stdClass();
        $oCur->id = 'some-id';
        $oCur->name = 'EUR';
        $oCur->rate = '1.00';
        $this->oxidConfig
            ->expects($this->any())
            ->method('getCurrencyArray')
            ->will($this->returnValue(array($oCur)));
        $this->oxidConfig
            ->expects($this->any())
            ->method('getShopId')
            ->will($this->returnValue('shop-id'));

        $this->mfBepadoConfiguration->setPurchaseGroup('B');
    }

    public function testFromBepadoToShop()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Product();
        $oProduct->price = 1.00;
        $oProduct->purchasePrice = 0.80;
        $oProduct->vat = 0.19;
        $oProduct->currency = 'EUR';

        $this->converter->fromBepadoToShop($oProduct, $oArticle);

        $this->assertEquals('1.19', $oArticle->getFieldData('oxprice'));
        $this->assertEquals('0.952', $oArticle->getFieldData('oxpriceb'), 0.05);
        $this->assertEquals('19', $oArticle->getFieldData('oxvat'));
    }

    public function testFromShopToBepado()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Product();
        $aParams = array(
            'oxarticles__oxprice'  => '1.00',
            'oxarticles__oxpriceb' => '0.80',
            'oxarticles__oxvat'    => '19',
        );
        $oArticle->assign($aParams);

        $this->moduleHelper
            ->expects($this->any())
            ->method('createNetPrice')
            ->will($this->returnValue(1.00));

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals(1.00, $oProduct->price);
        $this->assertEquals(1.00, $oProduct->purchasePrice);
        $this->assertEquals(0.19, $oProduct->vat);
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_module_helper'      => $this->moduleHelper,
            'mfBepadoConfiguration' => $this->mfBepadoConfiguration,
        );
    }

    protected function getConfigMapping()
    {
        return array(
            'blEnterNetPrice' => false,
        );
    }
}
