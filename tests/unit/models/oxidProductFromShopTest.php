<?php

require_once __DIR__.'/../BaseTestCase.php';

use Bepado\SDK\Struct as Struct;

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

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->productFromShop = new oxidProductFromShop();
        $this->productFromShop->setVersionLayer($this->versionLayer);
        $this->sdkHelper = $this->getMock('mf_sdk_helper', array('createSdkConfigFromOxid'));

        // create the objects for the mapping
        $this->oxUser = $this->getMockBuilder('oxUser')->disableOriginalConstructor()->getMock();
        $this->oxGroup = $this->getMockBuilder('oxGroups')->disableOriginalConstructor()->getMock();
        $this->oxBasket = $this->getMockBuilder('oxBasket')->disableOriginalConstructor()->getMock();
        $this->oxPayment = $this->getMockBuilder('oxPayment')->disableOriginalConstructor()->getMock();
        $this->oxPrice = $this->getMockBuilder('oxPrice')->disableOriginalConstructor()->getMock();
        $this->oxOrder = $this->getMockBuilder('oxOrder')->disableOriginalConstructor()->getMock();

        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));

        // preparing sdk and its config
        $this->sdk = $this->getMockBuilder('stdClass')->disableOriginalConstructor()->getMock();
        $this->sdkConfig = new SDKConfig();
        $this->sdkConfig->setApiEndpointUrl('test-endpoint');
        $this->sdkConfig->setApiKey('test-api-key');
        $this->sdkConfig->setProdMode(false);
        $this->sdkHelper->expects($this->any())->method('createSdkConfigFromOxid')->will($this->returnValue($this->sdkConfig));
        $this->sdkHelper->expects($this->any())->method('instantiateSdk')->will($this->returnValue($this->sdk));
    }

    /**
     * @expectedException \Exception
     * @expectedMessage "No user group for bepado remote shop found."
     */
    public function testBuyWithNonExistingUserGroup()
    {
        $this->oxGroup->expects($this->any())
            ->method('load')
            ->with($this->equalTo(oxidProductFromShop::BEPADO_USERGROUP_ID))
            ->will($this->returnValue(false));

        $this->productFromShop->buy(new Struct\Order());
    }

    /**
     * @expectedException \Exception
     * @expectedMessage "No valid products in basket"
     */
    public function testBuyWithEmptyBasket()
    {
        $this->oxGroup->expects($this->any())
            ->method('load')
            ->with($this->equalTo(oxidProductFromShop::BEPADO_USERGROUP_ID))
            ->will($this->returnValue(true));

        $address = new Struct\Address();
        $order = new Struct\Order();
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $orderItem = new Struct\OrderItem();
        $orderItem->count = 1;
        $orderItem->product = new Struct\Product();
        $order->orderItems[] = $orderItem;

        $this->productFromShop->buy($order);
    }

    /**
     * @expectedException \Exception
     * @expectedMessage "No Payment method found."
     */
    public function testBuyWithNoPaymentAction()
    {
        $this->oxGroup->expects($this->any())->method('load')->will($this->returnValue(true));
        $this->oxDb->expects($this->any())->method('getOne')->will($this->returnValue('some-id'));
        $address = new Struct\Address();
        $order = new Struct\Order();
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $orderItem = new Struct\OrderItem();
        $orderItem->count = 1;
        $orderItem->product = new Struct\Product();
        $order->orderItems[] = $orderItem;

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

        $address = new Struct\Address();
        $order = new Struct\Order();
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $order->billingAddress = $address;
        $order->deliveryAddress = $address;
        $orderItem = new Struct\OrderItem();
        $orderItem->count = 1;
        $orderItem->product = new Struct\Product();
        $orderItem->product->shopId = '__test__id__';
        $orderItem->product->sourceId = 'test';
        $orderItem->product->price = 0.99;
        $orderItem->product->purchasePrice = 0.89;
        $orderItem->product->availability = 3;
        $orderItem->product->title = 'Test Product';
        $orderItem->product->vendor = 'vendort-test';
        $orderItem->product->vat = 0.19;
        $order->orderItems[] = $orderItem;

        // expectations for called methods
        $this->oxPrice->expects($this->once())->method('setPrice')->with($this->equalTo(0));
        $this->oxBasket
            ->expects($this->once())
            ->method('setCost')
            ->with($this->equalTo('oxdelivery'), $this->equalTo($this->oxPrice));
        $this->oxOrder
            ->expects($this->once())
            ->method('finalizeOrder')
            ->with($this->equalTo($this->oxBasket), $this->equalTo($this->oxUser))
            ->will($this->returnValue('success-token'));
        $this->oxUser
            ->expects($this->once())
            ->method('onOrderExecute')
            ->with($this->equalTo($this->oxBasket), $this->equalTo('success-token'));
        $expectedId = 'some-id';
        $this->oxOrder
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($expectedId));
        $session->expects($this->once())->method('delBasket');

        $actualId = $this->productFromShop->buy($order);

        $this->assertEquals($expectedId, $actualId);
    }

    /**
     * @expectedException \Exception
     * @expectedMessage "No valid products in basket"
     */
    public function testReserveWithEmptyBasket()
    {
        $order = new Struct\Order();
        $order->orderItems = array();

        $this->productFromShop->reserve($order);
    }

    /**
     * @expectedException \Exception
     * @expectedMessage "Stock of articles is not valid"
     */
    public function testReserveWithInvalidStock()
    {
        $order = new Struct\Order();
        $order->orderItems = array();

        // expected method calls
        $this->oxOrder->expects($this->once())->method('validateStock')->will($this->returnValue(false));

        $this->productFromShop->reserve($order);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxuser'        => $this->oxUser,
            'oxgroups'      => $this->oxGroup,
            'oxbasket'      => $this->oxBasket,
            'oxpayment'     => $this->oxPayment,
            'oxprice'       => $this->oxPrice,
            'oxorder'       => $this->oxOrder,
            'mf_sdk_helper' => $this->sdkHelper,
        );
    }
}
 