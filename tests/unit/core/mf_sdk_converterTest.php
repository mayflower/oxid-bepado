<?php

require_once __DIR__.'/../BaseTestCase.php';

use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_converterTest extends BaseTestCase
{
    /**
     * @var mf_sdk_converter
     */
    protected $converter;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->converter = new mf_sdk_converter();
        $this->converter->setVersionLayer($this->versionLayer);
    }

    public function tearDown()
    {

    }

    public function testConvertToSDKProduct()
    {
        $oxArticle = oxNew('oxarticle');

        $product = $this->converter->toBepadoProduct($oxArticle);

        $this->assertNotNull($product);
    }

    public function testConvertFromSDKProduct()
    {
        $product = new Struct\Product();

        $oxArticle = $this->converter->toShopProduct($product);

        $this->assertNotNull($oxArticle);
    }

    protected function getObjectMapping()
    {
        return array();
    }
}
 