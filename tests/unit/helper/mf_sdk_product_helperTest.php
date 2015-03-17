<?php

use Bepado\SDK\Struct as Struct;
use Bepado\SDK\Struct\Reservation;

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../wrapper/sdkMock.php';

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_product_helperTest extends BaseTestCase
{
    protected $oxBasket;
    protected $oxBasketItem;
    protected $oxArticle;
    protected $sdkHelper;
    protected $sdk;
    protected $orderConverter;
    protected $oxOrder;
    protected $articleHelper;
    protected $loggerHelper;
    protected $oxUser;
    protected $addressConverter;

    /**
     * @var mf_sdk_product_helper
     */
    protected $helper;

    protected $bepadoConfiguration;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->helper = new mf_sdk_product_helper();
        $this->helper->setVersionLayer($this->versionLayer);

        // objects from oxid
        $this->oxBasket = $this->getMockBuilder('oxBasket')->disableOriginalConstructor()->getMock();
        $this->oxBasketItem = $this->getMockBuilder('oxBasketItem')->disableOriginalConstructor()->getMock();
        $this->oxArticle = $this->getMockBuilder('mf_bepado_oxarticle')->disableOriginalConstructor()->getMock();
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->orderConverter = $this->getMockBuilder('mf_sdk_order_converter')->disableOriginalConstructor()->getMock();
        $this->oxOrder = $this->getMockBuilder('oxOrder')->disableOriginalConstructor()->getMock();
        $this->articleHelper = $this->getMockBuilder('mf_sdk_article_helper')->disableOriginalConstructor()->getMock();
        $this->loggerHelper = $this->getMockBuilder('mf_sdk_logger_helper')->disableOriginalConstructor()->getMock();
        $this->oxUser = $this->getMockBuilder('oxUser')->disableOriginalConstructor()->getMock();
        $this->addressConverter = $this->getMockBuilder('mf_sdk_address_converter')->disableOriginalConstructor()->getMock();
        $this->bepadoConfiguration = $this->getMockBuilder('mfBepadoConfiguration')->disableOriginalConstructor()->getMock();

        $this->sdkHelper
            ->expects($this->any())
            ->method('computeConfiguration')
            ->will($this->returnValue($sdkConfig));
        $this->oxBasket
            ->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(array($this->oxBasketItem)));
        $this->oxBasketItem
            ->expects($this->any())
            ->method('getArticle')
            ->will($this->returnValue($this->oxArticle));
        $this->sdkHelper
            ->expects($this->any())
            ->method('instantiateSdk')
            ->will($this->returnValue($this->sdk));
        $this->loggerHelper
            ->expects($this->any())
            ->method('writeBepadoLog');
    }

    public function testCheckProductsInBasketNotImported()
    {
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(3));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(false));

        $this->helper->checkProductsInBasket($this->oxBasket);
    }

    public function testCheckProductsInBasketWithNoChanges()
    {
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(3));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->returnValue(true));

        $product = new Struct\Product();
        $product->availability = 3;
        $this->articleHelper
            ->expects($this->once())
            ->method('computeSdkProduct')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue($product));

        $this->helper->checkProductsInBasket($this->oxBasket);

        // asserts
        $this->assertEquals(new oxField('', oxField::T_TEXT), $this->oxBasketItem->bepado_check);
    }

    public function testCheckProductsInBasketWithPriceChanges()
    {
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(3));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->returnValue(array(
                    'some-id' => array(
                        new \Bepado\SDK\Struct\Message(
                            array(
                                'message' =>'Price changed.',
                                'values'  => array('price' => 10)
                            )
                        )
                    ))
            ));

        $product = new Struct\Product();
        $product->availability = 3;
        $this->articleHelper
            ->expects($this->once())
            ->method('computeSdkProduct')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue($product));
        $this->oxBasket
            ->expects($this->once())
            ->method('calculateBasket')
            ->with($this->equalTo(true));

        $this->helper->checkProductsInBasket($this->oxBasket);

        // asserts
        $this->assertEquals(new oxField('<ul><li><i>The price has changed.</i></li></ul>', oxField::T_TEXT), $this->oxBasketItem->bepado_check);
    }

    public function testCheckProductsInBasketWithAvailabilityChangesNoMatter()
    {
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(3));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->returnValue(array(
                    'some-id' =>
                        array(
                            new \Bepado\SDK\Struct\Message(
                                array(
                                    'message' =>'availability changed.',
                                    'values'  => array('availability' => 10)
                                )
                            ))
                )
            ));

        $product = new Struct\Product();
        $product->availability = 3;
        $this->articleHelper
            ->expects($this->once())
            ->method('computeSdkProduct')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue($product));

        $this->helper->checkProductsInBasket($this->oxBasket);

        // asserts
        $this->assertEquals(new oxField('', oxField::T_TEXT), $this->oxBasketItem->bepado_check);
    }

    public function testCheckProductsInBasketWithAvailabilityChangesDoesMatter()
    {
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(6));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->returnValue(array(
                'some-id' => array(
                        new \Bepado\SDK\Struct\Message(
                            array(
                                'message' =>'availability changed.',
                                'values'  => array('availability' => 5)
                            )
                        )
                ))
            ));

        $product = new Struct\Product();
        $product->availability = 3;
        $this->articleHelper
            ->expects($this->once())
            ->method('computeSdkProduct')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue($product));
        $this->oxBasket
            ->expects($this->once())
            ->method('calculateBasket')
            ->with($this->equalTo(true));

        $this->helper->checkProductsInBasket($this->oxBasket);

        // asserts
        $this->assertEquals(
            new oxField(
                '<ul><li><i>This product is available only 5 times. Either delete the
                        product from your basket or purchase the reduced amount.</i></li></ul>',
                oxField::T_TEXT
            ),
            $this->oxBasketItem->bepado_check
        );
    }

    public function testCheckProductsInBasketWithNoAvailability()
    {
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(6));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->returnValue(array(
                'some-id' => array(
                    new \Bepado\SDK\Struct\Message(
                        array(
                            'message' =>'availability changed.',
                            'values'  => array('availability' => 0)
                        )
                    )
                ))
            ));

        $product = new Struct\Product();
        $product->availability = 3;
        $this->articleHelper
            ->expects($this->once())
            ->method('computeSdkProduct')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue($product));
        $this->oxBasket
            ->expects($this->once())
            ->method('calculateBasket')
            ->with($this->equalTo(true));

        $this->helper->checkProductsInBasket($this->oxBasket);

        // asserts
        $this->assertEquals(
            new oxField(
                '<ul><li><i>This product is not available at the moment.</i></li></ul>',
                oxField::T_TEXT
            ),
            $this->oxBasketItem->bepado_check
        );
    }

    public function testCheckProductsInBasketWithExcption()
    {
        $exception = new \RuntimeException();
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(6));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->throwException($exception));

        $product = new Struct\Product();
        $product->availability = 3;
        $this->articleHelper
            ->expects($this->once())
            ->method('computeSdkProduct')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue($product));

        $this->helper->checkProductsInBasket($this->oxBasket);
    }

    public function testCheckProductsInBasketWithMarketplaceHint()
    {
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(6));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->returnValue(array()));

        $product = new Struct\Product();
        $product->shopId = 'shop-id';
        $product->availability = 3;
        $this->articleHelper
            ->expects($this->once())
            ->method('computeSdkProduct')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue($product));
        $this->oxBasket
            ->expects($this->never())
            ->method('calculateBasket');

        // prepare the markteplace shop
        $marketPlaceShop = new Struct\Shop();
        $marketPlaceShop->id = 'shop-id';
        $marketPlaceShop->url = 'some-url';
        $marketPlaceShop->name = 'some-name';
        $this->bepadoConfiguration
            ->expects($this->once())
            ->method('hastShopHintInBasket')
            ->will($this->returnValue(true));

        $this->sdkHelper
            ->expects($this->once())
            ->method('computeMarketplaceHintForProduct')
            ->with($this->equalTo($this->bepadoConfiguration), $this->equalTo($product))
            ->will($this->returnValue($marketPlaceShop))
        ;

        $this->helper->checkProductsInBasket($this->oxBasket);

        // asserts
        $this->assertEquals(
            $marketPlaceShop,
            $this->oxBasketItem->marketplace_shop
        );
    }

    public function testCheckProductsInBasketLogConfigurationError()
    {
        // expectations
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(0));
        $this->articleHelper
            ->expects($this->any())
            ->method('isArticleImported')
            ->will($this->returnValue(false));

        $this->oxBasket
            ->expects($this->never())
            ->method('calculateBasket');

        $this->bepadoConfiguration
            ->expects($this->once())
            ->method('isLoaded')
            ->will($this->returnValue(false));

        $this->loggerHelper
            ->expects($this->once())
            ->method('writeBepadoLog')
            ->with($this->equalTo('No bepado configuration found for shopId shop-id'));

        $this->helper->checkProductsInBasket($this->oxBasket);
    }

    public function testReservation()
    {
        $sdkOrder = new Struct\Order();
        $orderItem = new Struct\OrderItem();
        $sdkOrder->orderItems = array($orderItem);
        $sdkReservation = new Reservation();
        $sdkReservation->success = true;
        $oxOrder = $this->getMockBuilder('oxOrder')->disableOriginalConstructor()->getMock();

        // expected method calls
        $this->orderConverter
            ->expects($this->once())
            ->method('fromShopToBepado')
            ->with($this->equalTo($oxOrder))
            ->will($this->returnValue($sdkOrder));
        $this->sdk
            ->expects($this->once())
            ->method('reserveProducts')
            ->with($this->equalTo($sdkOrder))
            ->will($this->returnValue($sdkReservation));
        $this->addressConverter
            ->expects($this->any())
            ->method('fromShopToBepado')
            ->with($this->equalTo($this->oxUser), $this->equalTo('oxuser__ox'));

        $this->helper->reserveProductsInOrder($oxOrder, $this->oxUser);
    }

    public function testReservationWithEmptyOrderItems()
    {
        $sdkOrder = new Struct\Order();
        $oxOrder = $this->getMockBuilder('oxOrder')->disableOriginalConstructor()->getMock();

        // expected method calls
        $this->orderConverter
            ->expects($this->once())
            ->method('fromShopToBepado')
            ->with($this->equalTo($oxOrder))
            ->will($this->returnValue($sdkOrder));

        $result = $this->helper->reserveProductsInOrder($oxOrder, $this->oxUser);

        $this->assertFalse($result);
    }

    public function testCheckoutProduct()
    {
        $sdkReservation = new Reservation();
        $this->oxOrder
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(10));
        $this->sdk
            ->expects($this->once())
            ->method('checkout')
            ->with($this->equalTo($sdkReservation), $this->equalTo(10))
            ->will($this->returnValue(true));

        $result = $this->helper->checkoutProducts($sdkReservation, $this->oxOrder);

        $this->assertTrue($result);
    }

    protected function getObjectMapping()
    {
        return array(
            'mfBepadoConfiguration'    => $this->bepadoConfiguration,
            'mf_sdk_helper'            => $this->sdkHelper,
            'mf_sdk_order_converter'   => $this->orderConverter,
            'oxorder'                  => $this->oxOrder,
            'mf_sdk_article_helper'    => $this->articleHelper,
            'mf_sdk_logger_helper'     => $this->loggerHelper,
            'mf_sdk_address_converter' => $this->addressConverter,
        );
    }
}
