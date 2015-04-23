<?php
use Bepado\SDK\Struct\Product;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductVendorConverterTest extends BaseTestCase
{
    protected $oxShop;

    /**
     * @var mfProductVendorConverter
     */
    protected $converter;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->oxShop = $this->getMockBuilder('oxShop')->disableOriginalConstructor()->getMock();

        $this->converter = oxNew('mfProductVendorConverter');
        $this->converter->setVersionLayer($this->versionLayer);
    }

    public function testFromShopToBepadoWithArticlesVendor()
    {
        $oVendor = oxNew('oxVendor');
        $oVendor->assign(array('oxvendor__oxtitle' => 'vendor'));

        $oArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
        $oArticle
            ->expects($this->any())
            ->method('getVendor')
            ->will($this->returnValue($oVendor));
        $oProduct = new Product();

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals('vendor', $oProduct->vendor);
    }

    public function testFromShopToBepadoWithShopAsVendor()
    {
        $oArticle = oxNew('oxArticle');
        $oProduct = new Product();
        $this->oxShop
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('shop-id'));
        $this->oxShop
            ->expects($this->once())
            ->method('getFieldData')
            ->with($this->equalTo('oxshops__oxname'))
            ->will($this->returnValue('vendor'));

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals('vendor', $oProduct->vendor);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxShop' => $this->oxShop,
        );
    }
}
