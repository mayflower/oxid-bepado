<?php

use Bepado\SDK\Struct\SearchResult\Product;

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

    /**
     * @var mf_sdk_product_helper
     */
    private $helper;

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
        $sdkConfig = new SDKConfig();
        $this->sdkHelper
            ->expects($this->any())
            ->method('createSdkConfigFromOxid')
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
            ->with($this->equalTo($sdkConfig))
            ->will($this->returnValue($this->sdk));
    }

    public function testCheckProductsInBasket()
    {
        $this->oxBasketItem
            ->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(3));
        $this->oxArticle
            ->expects($this->any())
            ->method('isImportedFromBepado')
            ->will($this->returnValue(true));
        $this->sdk
            ->expects($this->any())
            ->method('checkProducts')
            ->will($this->returnValue(true));

        $product = new Product();
        $product->availability = 3;
        $this->oxArticle
            ->expects($this->once())
            ->method('getSdkProduct')
            ->will($this->returnValue($product));

            $this->helper->checkProductsInBasket($this->oxBasket);
    }

    protected function getObjectMapping()
    {
        return array(
            'SDKConfig'     => new SDKConfig(),
            'mf_sdk_helper' => $this->sdkHelper,
        );
    }
}
