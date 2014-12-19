<?php

require_once __DIR__.'/../BaseTestCase.php';

use Bepado\SDK\Struct as Struct;
use Bepado\SDK\Struct\Product;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class oxidProductFromShopTest extends BaseTestCase
{
    protected $sdkHelper;

    /**
     * @var oxidProductFromShop
     */
    protected $productFromShop;

    protected $oxGroup;
    protected $oxUser;
    protected $sdk;
    protected $sdkConfig;
    protected $oxDb;
    protected $oxBasket;
    protected $oxPayment;
    protected $oxPrice;
    protected $oxOrder;
    protected $converter;
    protected $oxArticle;
    protected $articleHelper;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->productFromShop = new oxidProductFromShop();
        $this->productFromShop->setVersionLayer($this->versionLayer);
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();

        // create the objects for the mapping
        $this->oxUser = $this->getMockBuilder('oxUser')->disableOriginalConstructor()->getMock();
        $this->oxGroup = $this->getMockBuilder('oxGroups')->disableOriginalConstructor()->getMock();
        $this->oxBasket = $this->getMockBuilder('oxBasket')->disableOriginalConstructor()->getMock();
        $this->oxPayment = $this->getMockBuilder('oxPayment')->disableOriginalConstructor()->getMock();
        $this->oxPrice = $this->getMockBuilder('oxPrice')->disableOriginalConstructor()->getMock();
        $this->oxOrder = $this->getMockBuilder('oxOrder')->disableOriginalConstructor()->getMock();
        $this->oxArticle = $this->getMockBuilder('mf_bepado_oxarticle')->disableOriginalConstructor()->getMock();
        $this->converter = $this->getMockBuilder('mf_sdk_converter')->disableOriginalConstructor()->getMock();
        $this->articleHelper = $this->getMockBuilder('mf_sdk_article_helper')->disableOriginalConstructor()->getMock();
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();

        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));

        // preparing sdk and its config
        $this->sdkConfig = new SDKConfig();
        $this->sdkConfig->setApiEndpointUrl('test-endpoint');
        $this->sdkConfig->setApiKey('test-api-key');
        $this->sdkConfig->setProdMode(false);
        $this->sdkHelper->expects($this->any())->method('createSdkConfigFromOxid')->will($this->returnValue($this->sdkConfig));
        $this->sdkHelper->expects($this->any())->method('instantiateSdk')->will($this->returnValue($this->sdk));
        $this->oxArticle->expects($this->any())->method('getId')->will($this->returnValue('some-id'));
    }

    public function tearDown()
    {
        unset(
            $this->oxArticle,
            $this->oxOrder,
            $this->oxBasket,
            $this->oxDb,
            $this->oxUser,
            $this->oxGroup,
            $this->oxPayment
        );
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Shop with id some-id not known
     */
    public function testBuyWithNoShop()
    {
        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with('some-id')
            ->will($this->returnValue(false));
        $order = new Struct\Order();
        $order->providerShop = 'some-id';


        $this->productFromShop->buy($order);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No user group for bepado remote shop found.
     */
    public function testBuyWithNonExistingUserGroup()
    {
        $this->oxGroup->expects($this->any())
            ->method('load')
            ->with($this->equalTo(oxidProductFromShop::BEPADO_USERGROUP_ID))
            ->will($this->returnValue(false));
        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with('some-id')
            ->will($this->returnValue(true));
        $order = new Struct\Order();
        $order->providerShop = 'some-id';

        $this->productFromShop->buy($order);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No valid products in basket
     */
    public function testBuyWithEmptyBasket()
    {
        $this->oxGroup->expects($this->any())
            ->method('load')
            ->with($this->equalTo(oxidProductFromShop::BEPADO_USERGROUP_ID))
            ->will($this->returnValue(true));

        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with($this->equalTo('some-id'))
            ->will($this->returnValue(true));
        $address = new Struct\Address();
        $order = new Struct\Order();
        $order->providerShop = 'some-id';

        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $order->orderItems = array();

        $this->productFromShop->buy($order);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No Payment method found.
     */
    public function testBuyWithNoPaymentAction()
    {
        $this->oxGroup->expects($this->any())->method('load')->will($this->returnValue(true));
        $this->oxDb->expects($this->any())->method('getOne')->will($this->returnValue(null));
        $address = new Struct\Address();
        $order = new Struct\Order();
        $order->providerShop = 'some-id';
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $orderItem = new Struct\OrderItem();
        $orderItem->count = 1;
        $orderItem->product = new Struct\Product();
        $order->orderItems[] = $orderItem;

        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with($this->equalTo('some-id'))
            ->will($this->returnValue(true));
        $this->converter
            ->expects($this->once())
            ->method('fromBepadoToShop')
            ->with($this->equalTo($orderItem->product))
            ->will($this->returnValue($this->oxArticle));
        $this->oxBasket
            ->expects($this->once())
            ->method('addToBasket')
            ->with($this->equalTo('some-id'))
            ;
        $this->oxBasket->expects($this->once())->method('calculateBasket')->with($this->equalTo(true));
        $this->oxBasket->expects($this->any())->method('getProductsCount')->will($this->returnValue(1));

        $this->productFromShop->buy($order);
    }

    public function testBuy()
    {
        $this->oxGroup->expects($this->any())->method('load')->will($this->returnValue(true));
        $this->oxDb->expects($this->any())->method('getOne')->will($this->returnValue('some-id'));
        $session = $this->getMockBuilder('oxSession')->disableOriginalConstructor()->getMock();
        $this->versionLayer
            ->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($session));
        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with($this->equalTo('some-id'))
            ->will($this->returnValue(true));
        $address = new Struct\Address();
        $order = new Struct\Order();
        $order->providerShop = 'some-id';
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $orderItem = new Struct\OrderItem();
        $orderItem->count = 1;
        $orderItem->product = new Struct\Product();
        $order->orderItems[] = $orderItem;

        // expectations for called methods
        $this->oxBasket
            ->expects($this->once())
            ->method('setCost')
            ->with($this->equalTo('oxdelivery'), $this->equalTo($this->oxPrice));
        $this->oxOrder
            ->expects($this->once())
            ->method('finalizeOrder')
            ->with($this->equalTo($this->oxBasket), $this->equalTo($this->oxUser))
            ->will($this->returnValue('success-token'));
        $this->oxUser->expects($this->once())->method('onOrderExecute')->with($this->equalTo($this->oxBasket), $this->equalTo('success-token'));
        $expectedId = 'some-id';
        $this->oxOrder->expects($this->once())->method('getId')->will($this->returnValue($expectedId));

        $this->converter
            ->expects($this->once())
            ->method('fromBepadoToShop')
            ->with($this->equalTo($orderItem->product))
            ->will($this->returnValue($this->oxArticle));
        $this->oxBasket
            ->expects($this->once())
            ->method('addToBasket')
            ->with($this->equalTo('some-id'))
        ;
        $this->oxBasket->expects($this->once())->method('calculateBasket')->with($this->equalTo(true));
        $this->oxBasket
            ->expects($this->any())
            ->method('getProductsCount')
            ->will($this->returnValue(1));
        $shippingCosts = new Struct\ShippingCosts();
        $shippingCosts->shippingCosts = 10;
        $shippingCosts->grossShippingCosts = 10*1.19;
        $this->sdk
            ->expects($this->once())
            ->method('calculateShippingCosts')
            ->with($this->equalTo($order))
            ->will($this->returnValue($shippingCosts));
        $this->oxPrice->expects($this->once())->method('setPrice')->with($this->equalTo(10), $this->equalTo(0.19));

        $session->expects($this->once())->method('delBasket');

        $actualId = $this->productFromShop->buy($order);

        $this->assertEquals($expectedId, $actualId);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No valid products in basket
     */
    public function testReserveWithEmptyBasket()
    {
        $order = new Struct\Order();
        $order->orderItems = array();

        $this->productFromShop->reserve($order);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Stock of articles is not valid
     */
    public function testReserveWithInvalidStock()
    {
        $order = new Struct\Order();
        $orderItem = new Struct\OrderItem();
        $product = new Product();
        $orderItem->count = 3;
        $orderItem->product = $product;
        $order->orderItems[] = $orderItem;
        $this->converter
            ->expects($this->once())
            ->method('fromBepadoToShop')
            ->with($this->equalTo($product))
            ->will($this->returnValue($this->oxArticle));
        $this->oxBasket->expects($this->once())->method('addToBasket')->with($this->equalTo('some-id'), $this->equalTo(3));
        $this->oxBasket->expects($this->once())->method('calculateBasket')->with($this->equalTo(true));
        $this->oxBasket
            ->expects($this->once())
            ->method('getProductsCount')
            ->will($this->returnValue(3));

        // expected method calls
        $this->oxOrder->expects($this->once())->method('validateStock')->will($this->returnValue(false));

        $this->productFromShop->reserve($order);
    }

    public function testGetProductsNoExportedProducts()
    {
        $this->oxArticle
            ->expects($this->any())
            ->method('load')
            ->with($this->equalTo('some-id'))
            ->will($this->returnValue(true));
        $this->oxArticle
            ->expects($this->any())
            ->method('readyForExportToBepado')
            ->will($this->returnValue(false));

        $actual = $this->productFromShop->getProducts(array('some-id'));

        $this->assertCount(0, $actual);
    }

    public function testGetProductsWithNonExistingArticle()
    {
        $this->oxArticle
            ->expects($this->any())
            ->method('load')
            ->with($this->equalTo('some-id'));
        $this->oxArticle->expects($this->once())->method('isLoaded')->will($this->returnValue(false));
        $this->oxArticle->expects($this->any())->method('readyForExportToBepado')->will($this->returnValue(true));

        $actual = $this->productFromShop->getProducts(array('some-id'));

        $this->assertCount(0, $actual);
    }

    public function testGetProducts()
    {
        $this->oxArticle->expects($this->any())->method('load')->with($this->equalTo('some-id'));
        $this->oxArticle->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->converter->expects($this->once())->method('fromShopToBepado')->will($this->returnValue(new Struct\Product()));

        $this->articleHelper
            ->expects($this->once())
            ->method('isArticleExported')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue(true));

        $actual = $this->productFromShop->getProducts(array('some-id'));

        $this->assertCount(1, $actual);
        $actualArticle = array_shift($actual);
        $this->assertEquals(new Struct\Product(), $actualArticle);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxuser'                => $this->oxUser,
            'oxgroups'              => $this->oxGroup,
            'oxbasket'              => $this->oxBasket,
            'oxpayment'             => $this->oxPayment,
            'oxprice'               => $this->oxPrice,
            'oxorder'               => $this->oxOrder,
            'mf_sdk_converter'      => $this->converter,
            'oxarticle'             => $this->oxArticle,
            'mf_sdk_helper'         => $this->sdkHelper,
            'mf_sdk_article_helper' => $this->articleHelper,
        );
    }
}
