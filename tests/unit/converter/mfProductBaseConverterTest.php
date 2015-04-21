<?php
use Bepado\SDK\Struct\Product;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductBaseConverterTest extends BaseTestCase
{
    /**
     * @var mfProductBaseConverter
     */
    protected $converter;

    public function setUp()
    {
        $this->converter = oxNew('mfProductBaseConverter');
    }

    public function testFromShopToBepado()
    {
        /** @var oxArticle $oArticle */
        $oArticle = oxNew('oxArticle');
        $aParams = array(
            'oxarticles__oxean'       => 'ean',
            'oxarticles__oxid'        => 'id',
            'oxarticles__oxtitle'     => 'title',
            'oxarticles__oxshortdesc' => 'short description'
        );
        $oArticle->setArticleLongDesc('long description');
        $oArticle->assign($aParams);
        $oProduct = new Product();

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals('ean', $oProduct->ean);
        $this->assertEquals('id', $oProduct->sourceId);
        $this->assertEquals('title', $oProduct->title);
        $this->assertEquals('http://www.oxid-test.dev/index.php?cl=details&amp;anid=id', $oProduct->url);
        $this->assertEquals('long description', $oProduct->longDescription);
        $this->assertEquals('short description', $oProduct->shortDescription);

    }

    protected function getObjectMapping()
    {
        return array();
    }
}
