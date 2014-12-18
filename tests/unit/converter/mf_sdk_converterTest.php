<?php

require_once __DIR__ . '/../BaseTestCase.php';

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
    protected $oxShop;

    protected $productValues = array(
        'sourceId'         => 'some-id',
        'ean'              => 'test-ean',
        'url'              => '',
        'title'            => 'test-title',
        'shortDescription' => 'test short description',
        'longDescription'  => 'test long description',
        'vendor'           => 'test-vendor',
        'vat'              => 0.19,      # should come from the shop directly
        'price'            => 92.44,
        'purchasePrice'    => 90,
        'fixedPrice'       => false,   # won't skip this in usual shops
        'currency'         => 'EUR',
        'freeDelivery'     => false,
        'deliveryDate'     => null,
        'availability'     => 10,
        'images'           => array('imgage-url-1'),
        'categories'       => array(),
        'tags'             => array(),
        'relevance'        => 0,
        'attributes'       => array(
            Struct\Product::ATTRIBUTE_WEIGHT => 11,
            Struct\Product::ATTRIBUTE_VOLUME => '2184',
            Struct\Product::ATTRIBUTE_DIMENSION => '13x12x14',
            Struct\Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Struct\Product::ATTRIBUTE_QUANTITY => 10,
            Struct\Product::ATTRIBUTE_UNIT => 'g'
        ),
        'deliveryWorkDays' => null,
    );

    protected $articleValues = array(
        'oxarticles__oxid'           => 'some-id',
        'oxarticles__oxean'          => 'test-ean',
        'oxarticles__oxtitle'        => 'test-title',
        'oxarticles__oxshortdesc'    => 'test short description',
        'oxarticles__oxprice'        => 92.44,
        'oxarticles__oxpricea'       => 90,
        'oxarticles__oxstock'        => 10,
        'oxarticles__oxweight'       => 11,
        'oxarticles__oxwidth'        => 12,
        'oxarticles__oxlength'       => 13,
        'oxarticles__oxheight'       => 14,
        'oxarticles__oxunitquantity' => 10,
        'oxarticles__oxunitname'     => '_UNIT_G',
        'oxarticles__oxpic1'         => 'imgage-url-1',
    );

    protected $sdkHelper;
    protected $oxList;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->converter = new mf_sdk_converter();
        $this->converter->setVersionLayer($this->versionLayer);

        // oxid objects
        $this->oxShop = $this->getMockBuilder('oxShop')->disableOriginalConstructor()->getMock();
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->oxList = $this->getMockBuilder('oxList')->disableOriginalConstructor()->getMock();

        // expected method with its values to return
        $currencyItem = new \stdClass();
        $currencyItem->rate = '1.00';
        $currencyItem->name = 'EUR';
        $this->oxidConfig
            ->expects($this->once())
            ->method('getCurrencyArray')
            ->will($this->returnValue(array($currencyItem)));
    }

    /**
     * @dataProvider provideProductValues
     */
    public function testConvertToSDKProduct($productProperty, $productValue, $testable = true)
    {
        $shopUrl = oxRegistry::getConfig()->getShopUrl();
        $this->productValues['url'] = $shopUrl . 'index.php?cl=details&amp;anid=some-id';

        /** @var oxArticle $oxArticle */
        $oxArticle = oxNew('oxarticle');
        $oxArticle->assign($this->articleValues);

        $product = $this->converter->fromShopToBepado($oxArticle);

        if ($testable) {
            $this->assertEquals($productValue, $product->$productProperty);
        } else {
            $this->markTestIncomplete('Can not test for Property: '.$productProperty);
        }
    }

    /**
     * Can not test the following properties atm:
     *  - vendor: cause its set on some cruell ways by the shop, got no influence atm
     *  - longDescription: some strange setter in oxArticle
     *
     * @return array
     */
    public function provideProductValues()
    {
        $values = array();
        $shopUrl = oxRegistry::getConfig()->getShopUrl();
        $this->productValues['url'] = $shopUrl . 'index.php?cl=details&amp;anid=some-id';

        foreach ($this->productValues as $property => $value) {
            $testable = in_array($property, array('vendor', 'longDescription')) ? false : true;
            if ('images' === $property) {
                $value = array($shopUrl . 'out/pictures/generated/product/1/380_340_75/nopic.jpg'); // default image for non existing images
            }

            if (!oxRegistry::getConfig()->getConfigParam('blEnterNetPrice')) {
                if ('price' === $property) {
                    $value = 77.68;
                } elseif ('purchasePrice' === $property) {
                    $value = 75.63;
                }
            }

            $values[] = array($property, $value, $testable);
        }

        return $values;
    }

    /**
     * @dataProvider provideArticleValues
     */
    public function testConvertFromSDKProduct($field, $fieldValue, $testable)
    {
        $product = new Struct\Product();
        $shopUrl = oxRegistry::getConfig()->getShopUrl();
        $this->productValues['url'] = $shopUrl . 'index.php?cl=details&amp;anid=some-id';

        foreach ($this->productValues as $property => $propertyValue) {
            $product->$property = $propertyValue;
        }

        // expected behavior of helper
        $this->sdkHelper
            ->expects($this->any())
            ->method('createOxidImageFromPath')
            ->with($this->equalTo('imgage-url-1'), $this->equalTo(1))
            ->will($this->returnValue(array('oxarticles__oxpic1', 'imgage-url-1')))
        ;
        $oxArticle = $this->converter->fromBepadoToShop($product);

        if ($testable) {
            $actualValue = $oxArticle->getFieldData($field);
            if ('oxarticles__oxid' !== $field) {
                $this->assertEquals($fieldValue, $actualValue);
            } else {
                $this->assertNull($actualValue, 'We do not want to set the oxid from a sdk product at that point.');
            }

        } else {
            $this->markTestIncomplete('Can not test for field: '.$field);
        }
    }

    public function provideArticleValues()
    {
        $values = array();
        foreach ($this->articleValues as $field => $value) {
            $testable = in_array($field, array()) ? false : true;
            if (!oxRegistry::getConfig()->getConfigParam('blEnterNetPrice')) {
                if ('oxarticles__oxprice' === $field) {
                    $value = 110.00359999999999;
                } elseif ('oxarticles__oxpricea' === $field) {
                    $value = 107.10;
                }
            }
            $values[] = array($field, $value, $testable);
        }

        return $values;
    }

    public function testEmptyPurchasePriceShouldUseRegularPriceWhenNull()
    {
        $shopUrl = oxRegistry::getConfig()->getShopUrl();
        $this->productValues['url'] = $shopUrl . 'index.php?cl=details&amp;anid=some-id';

        $this->articleValues['oxarticles__oxpricea'] = null;

        /** @var oxArticle $oxArticle */
        $oxArticle = oxNew('oxarticle');
        $oxArticle->assign($this->articleValues);

        $product = $this->converter->fromShopToBepado($oxArticle);

        $this->assertEquals($product->price, $product->purchasePrice);
    }

    public function testEmptyPurchasePriceShouldUseRegularPriceWhenZero()
    {
        $shopUrl = oxRegistry::getConfig()->getShopUrl();
        $this->productValues['url'] = $shopUrl . 'index.php?cl=details&amp;anid=some-id';

        $this->articleValues['oxarticles__oxpricea'] = 0.0;

        /** @var oxArticle $oxArticle */
        $oxArticle = oxNew('oxarticle');
        $oxArticle->assign($this->articleValues);

        $product = $this->converter->fromShopToBepado($oxArticle);

        $this->assertEquals($product->price, $product->purchasePrice);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxshop'         => $this->oxShop,
            'mf_sdk_helper'  => $this->sdkHelper,
            'oxlist'         => $this->oxList,
        );
    }
}
