<?php

use Bepado\SDK\Struct\Product;
use Bepado\SDK\Struct as Struct;

require_once __DIR__ . '/../BaseTestCase.php';

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_article_helperTest extends BaseTestCase
{

    /**
     * @var mf_sdk_article_helper
     */
    protected $helper;

    /**
     * @var oxArticle
     */
    protected $oxArticle;

    /**
     * @var oxOrderArticle
     */
    protected $oxOrderArticle;
    protected $oxDb;
    protected $sdkHelper;
    protected $sdk;
    protected $productConverter;
    protected $bepadoConfiguration;
    protected $loggerHelper;
    protected $bepadoProduct;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->helper = new mf_sdk_article_helper();
        $this->helper->setVersionLayer($this->versionLayer);

        // create article and its order representation
        $this->oxArticle = oxNew('oxArticle');
        $this->oxArticle->assign(array('oxid' => 'test-id'));
        $this->oxOrderArticle = oxNew('oxOrderArticle');
        $this->oxOrderArticle->setArticle($this->oxArticle);

        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->productConverter = $this->getMockBuilder('mf_sdk_converter')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->sdkHelper
            ->expects($this->any())
            ->method('instantiateSdk')
            ->will($this->returnValue($this->sdk));
        $this->bepadoConfiguration = $this->getMockBuilder('mfBepadoConfiguration')->disableOriginalConstructor()->getMock();
        $this->bepadoProduct = $this->getMockBuilder('mfBepadoProduct')->disableOriginalConstructor()->getMock();
        $this->loggerHelper = $this->getMockBuilder('mf_sdk_logger_helper')->disableOriginalConstructor()->getMock();
    }

    public function tearDown()
    {
        unset($this->helper, $this->oxArticle, $this->oxOrderArticle, $this->versionLayer);
    }

    protected function returnBepadoProductStateNone()
    {
        $this->bepadoProduct->expects($this->once())
            ->method('getState')
            ->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_NONE));
    }

    protected function returnBepadoProductStateExported()
    {
        $this->bepadoProduct->expects($this->once())
            ->method('getState')
            ->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_EXPORTED));
    }

    protected function returnBepadoProductStateImported()
    {
        $this->bepadoProduct->expects($this->once())
            ->method('getState')
            ->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_IMPORTED));
    }

    public function testArticleThatIsNotTracked()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->isArticleExported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testNotExportedArticle()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->isArticleExported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testExportedArticle()
    {
        $this->returnBepadoProductStateExported();
        $result = $this->helper->isArticleExported($this->oxArticle);

        $this->assertTrue($result);
    }

    public function testArticleImportedForNonTrackedOnes()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->isArticleImported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testNotImportedArticle()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->isArticleImported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testImportedArticle()
    {
        $this->returnBepadoProductStateImported();
        $result = $this->helper->isArticleImported($this->oxArticle);

        $this->assertTrue($result);
    }

    public function testOrderArticleImportedForNonTrackedOnes()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->isOrderArticleImported($this->oxOrderArticle);

        $this->assertFalse($result);
    }

    public function testNotImportedOrderArticle()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->isOrderArticleImported($this->oxOrderArticle);

        $this->assertFalse($result);
    }

    public function testImportedOrderArticle()
    {
        $this->returnBepadoProductStateImported();
        $result = $this->helper->isOrderArticleImported($this->oxOrderArticle);

        $this->assertTrue($result);
    }

    public function testGetArticleStateForNonTrackedOnes()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(0, $result);
    }

    public function testNoArticleState()
    {
        $this->returnBepadoProductStateNone();
        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(0, $result);
    }

    public function testExportedArticleState()
    {
        $this->returnBepadoProductStateExported();
        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(1, $result);
    }

    public function testImportedArticleState()
    {
        $this->returnBepadoProductStateImported();
        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(2, $result);
    }

    public function testOnSaveArticleExtendDeleteExported()
    {
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo($this->oxArticle->getId()));
        $this->bepadoProduct->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->bepadoProduct->expects($this->once())->method('delete');
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "0")));

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnSaveArticleExtendNothingShouldHappenWhenNotFoundAndStateFalse()
    {
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo($this->oxArticle->getId()));
        $this->bepadoProduct->expects($this->at(0))->method('isLoaded')->will($this->returnValue(false));
        $this->bepadoProduct->expects($this->at(1))->method('isLoaded')->will($this->returnValue(true));
        $this->bepadoProduct->expects($this->never())->method('assign');
        $this->bepadoProduct->expects($this->never())->method('save');
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "0")));

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnSaveArticleExtendNothingShouldHapenWhenFoundAndStateTrue()
    {
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo($this->oxArticle->getId()));
        $this->bepadoProduct->expects($this->at(0))->method('isLoaded')->will($this->returnValue(false));
        $this->bepadoProduct->expects($this->at(1))->method('isLoaded')->will($this->returnValue(true));
        $this->bepadoProduct->expects($this->never())->method('delete');
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "1")));

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnSaveArticleExtendSaveNewEntry()
    {
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo($this->oxArticle->getId()));
        $this->bepadoProduct->expects($this->any())->method('isLoaded')->will($this->returnValue(false));
        $this->bepadoProduct
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo(array(
                'p_source_id' => 'test-id',
                'OXID'        => 'test-id',
                'shop_id'     => '_self_',
                'state'       => 1
            )));
        $this->bepadoProduct->expects($this->once())->method('save');
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "1")));

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnArticleDeleteWithUnknownArticle()
    {
        $resultSet = $this->getMockBuilder('object_ResultSet')->disableOriginalConstructor()->getMock();
        $resultSet->expects($this->once())->method('getArray')->will($this->returnValue(array()));

        $this->oxDb
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo("SELECT * FROM mfbepadoproducts WHERE `OXID` LIKE 'test-id'"))
            ->will($this->returnValue($resultSet));

        $this->helper->onArticleDelete($this->oxArticle);
    }

    public function testOnArticleDeleteWithKnownArticle()
    {
        $resultSet = $this->getMockBuilder('object_ResultSet')->disableOriginalConstructor()->getMock();
        $resultSet->expects($this->once())->method('getArray')->will($this->returnValue(array('1', '2')));

        $this->oxDb
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo("SELECT * FROM mfbepadoproducts WHERE `OXID` LIKE 'test-id'"))
            ->will($this->returnValue($resultSet));

        $this->sdk->expects($this->once())->method('recordDelete');

        $this->helper->onArticleDelete($this->oxArticle);
    }

    public function testOnArticleSaveNotKnownAndNotExportedShouldDoNothing()
    {
        $this->isArticleExportedShouldReturn(false);
        $this->isKnownShouldReturn(false);

        $this->sdk->expects($this->never())->method('recordDelete');
        $this->sdk->expects($this->never())->method('recordInsert');
        $this->sdk->expects($this->never())->method('recordUpdate');

        $this->helper->onArticleSave($this->oxArticle);
    }

    public function testOnArticleSaveKnownAndExportedShouldUpdate()
    {
        $this->isArticleExportedShouldReturn(true);
        $this->isKnownShouldReturn(true);

        $this->sdk->expects($this->never())->method('recordDelete');
        $this->sdk->expects($this->never())->method('recordInsert');
        $this->sdk->expects($this->once())->method('recordUpdate');

        $this->helper->onArticleSave($this->oxArticle);
    }

    public function testOnArticleSaveKnownAndNotExportedShouldShouldDelete()
    {
        $this->isArticleExportedShouldReturn(false);
        $this->isKnownShouldReturn(true);

        $this->sdk->expects($this->once())->method('recordDelete');
        $this->sdk->expects($this->never())->method('recordInsert');
        $this->sdk->expects($this->never())->method('recordUpdate');

        $this->helper->onArticleSave($this->oxArticle);
    }

    public function testOnArticleSaveNotKnownAndExportedShouldInsert()
    {
        $this->isArticleExportedShouldReturn(true);
        $this->isKnownShouldReturn(false);

        $this->sdk->expects($this->never())->method('recordDelete');
        $this->sdk->expects($this->once())->method('recordInsert');
        $this->sdk->expects($this->never())->method('recordUpdate');

        $this->helper->onArticleSave($this->oxArticle);
    }

    protected function isArticleExportedShouldReturn($value)
    {
        $this->bepadoProduct->expects($this->any())->method('init')->with($this->equalTo('mfbepadoproducts'));
        $this->bepadoProduct->expects($this->any())->method('load')->with($this->equalTo('test-id'));
        $returnValue = ($value) ? mfBepadoProduct::PRODUCT_STATE_EXPORTED : mfBepadoProduct::PRODUCT_STATE_NONE;
        $this->bepadoProduct
            ->expects($this->once())
            ->method('getState')
            ->will($this->returnValue($returnValue));
    }

    protected function isKnownShouldReturn($value)
    {
        $resultSet = $this->getMockBuilder('object_ResultSet')->disableOriginalConstructor()->getMock();
        $resultSet
            ->expects($this->once())
            ->method('getArray')
            ->will($this->returnValue($value ? array('1', '2') : array()));
        $this->oxDb
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo("SELECT * FROM bepado_product WHERE `p_source_id` LIKE 'test-id'"))
            ->will($this->returnValue($resultSet));
    }

    /**
     * @expectedException \Exception
     * @expectedMessage "Article is not managed for bepado. Neither exported to a remote shop nor imported."
     */
    public function testComputeToSDKProductThrowsExceptionWhenArticleIsNotManaged()
    {
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct
            ->expects($this->once())
            ->method('getState')
            ->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_NONE));

        $this->helper->computeSdkProduct($this->oxArticle);
    }

    public function testComputeToSDKProduct()
    {
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct->expects($this->once())->method('getShopId')->will($this->returnValue('shop-id'));
        $this->bepadoProduct->expects($this->once())->method('getProductSourceId')->will($this->returnValue('source-id'));
        $this->bepadoProduct->expects($this->once())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_EXPORTED));
        $this->productConverter
            ->expects($this->once())
            ->method('fromShopToBepado')
            ->will($this->returnValue(new Product()));

        $product = $this->helper->computeSdkProduct($this->oxArticle);

        $this->assertInstanceOf(get_class(new Product()), $product);
        $this->assertEquals('source-id', $product->sourceId);
        $this->assertEquals('shop-id', $product->shopId);
    }

    public function testMarketHintCreation()
    {
        $this->bepadoProduct->expects($this->any())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct->expects($this->any())->method('getShopId')->will($this->returnValue('shop-id'));
        $this->bepadoProduct->expects($this->any())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_IMPORTED));
        $this->bepadoConfiguration->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        // prepare the markteplace shop
        $marketPlaceShop = new Struct\Shop();
        $marketPlaceShop->id = 'shop-id';
        $marketPlaceShop->url = 'some-url';
        $marketPlaceShop->name = 'some-name';
        $this->bepadoConfiguration
            ->expects($this->once())
            ->method('hastShopHintOnArticleDetails')
            ->will($this->returnValue(true));
        $this->sdkHelper
            ->expects($this->once())
            ->method('computeMarketplaceHintForProduct')
            ->with($this->equalTo($this->bepadoConfiguration))
            ->will($this->returnValue($marketPlaceShop))
            ;
        $this->productConverter
            ->expects($this->once())
            ->method('fromShopToBepado')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue(new Product()));

        $this->helper->computeMarketplaceHintOnArticle($this->oxArticle);

        $this->assertEquals($marketPlaceShop, $this->oxArticle->marketplace_shop);
    }

    public function testMarketHintCreationInvalidModuleConfigurationShouldBeLogged()
    {
        $this->bepadoProduct->expects($this->any())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct->expects($this->any())->method('getShopId')->will($this->returnValue('shop-id'));
        $this->bepadoProduct->expects($this->any())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_IMPORTED));
        $this->bepadoConfiguration
            ->expects($this->once())
            ->method('isLoaded')->will($this->returnValue(false));
        $this->loggerHelper
            ->expects($this->once())
            ->method('writeBepadoLog')
            ->with($this->equalTo('No bepado configuration found for shopId shop-id'));

        $result = $this->helper->computeMarketplaceHintOnArticle($this->oxArticle);

        $this->assertNull($result);
    }

    public function testMarketHintCreationForNonImportedArticlesShouldDoNothing()
    {
        $this->bepadoProduct->expects($this->any())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct->expects($this->never())->method('getShopId')->will($this->returnValue('shop-id'));
        $this->bepadoProduct->expects($this->once())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_EXPORTED));
        $this->bepadoConfiguration
            ->expects($this->never())
            ->method('load');
        $result = $this->helper->computeMarketplaceHintOnArticle($this->oxArticle);

        $this->assertNull($result);
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_helper'         => $this->sdkHelper,
            'mf_sdk_converter'      => $this->productConverter,
            'mfBepadoConfiguration' => $this->bepadoConfiguration,
            'mf_sdk_logger_helper'  => $this->loggerHelper,
            'mfBepadoProduct'       => $this->bepadoProduct,
        );
    }
}
