<?php


use Bepado\SDK\Struct\Product;

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
    protected $oxBase;
    protected $oxDb;
    protected $sdkHelper;
    protected $sdk;
    protected $productConverter;

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

        $this->oxBase = $this->getMockBuilder('oxBase')->disableOriginalConstructor()->getMock();
        $this->oxBase->expects($this->any())->method('init');
        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->productConverter = $this->getMockBuilder('mf_sdk_converter')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $sdkConfig = new mfBepadoConfiguration();
        $this->sdkHelper
            ->expects($this->any())
            ->method('createSdkConfigFromOxid')
            ->will($this->returnValue($sdkConfig));
        $this->sdkHelper
            ->expects($this->any())
            ->method('instantiateSdk')
            ->with($this->equalTo($sdkConfig))
            ->will($this->returnValue($this->sdk));
    }

    public function tearDown()
    {
        unset($this->helper, $this->oxArticle, $this->oxOrderArticle, $this->oxBase, $this->versionLayer);
    }

    public function testArticleThatIsNotTracked()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(false));

        $result = $this->helper->isArticleExported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testNotExportedArticle()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase->expects($this->once())->method('getFieldData')->will($this->returnValue(null));

        $result = $this->helper->isArticleExported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testExportedArticle()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase
            ->expects($this->once())
            ->method('getFieldData')
            ->will($this->returnValue((string) mfBepadoConfiguration::ARTICLE_STATE_EXPORTED));

        $result = $this->helper->isArticleExported($this->oxArticle);

        $this->assertTrue($result);
    }

    public function testArticleImportedForNonTrackedOnes()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(false));

        $result = $this->helper->isArticleImported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testNotImportedArticle()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase->expects($this->once())->method('getFieldData')->will($this->returnValue(null));

        $result = $this->helper->isArticleImported($this->oxArticle);

        $this->assertFalse($result);
    }

    public function testImportedArticle()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase
            ->expects($this->once())
            ->method('getFieldData')
            ->will($this->returnValue((string) mfBepadoConfiguration::ARTICLE_STATE_IMPORTED));

        $result = $this->helper->isArticleImported($this->oxArticle);

        $this->assertTrue($result);
    }

    public function testOrderArticleImportedForNonTrackedOnes()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(false));

        $result = $this->helper->isOrderArticleImported($this->oxOrderArticle);

        $this->assertFalse($result);
    }

    public function testNotImportedOrderArticle()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase->expects($this->once())->method('getFieldData')->will($this->returnValue(null));

        $result = $this->helper->isOrderArticleImported($this->oxOrderArticle);

        $this->assertFalse($result);
    }

    public function testImportedOrderArticle()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase
            ->expects($this->once())
            ->method('getFieldData')
            ->will($this->returnValue((string) mfBepadoConfiguration::ARTICLE_STATE_IMPORTED));

        $result = $this->helper->isOrderArticleImported($this->oxOrderArticle);

        $this->assertTrue($result);
    }

    public function testGetArticleStateForNonTrackedOnes()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(false));

        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(0, $result);
    }

    public function testNoArticleState()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase
            ->expects($this->once())
            ->method('getFieldData')
            ->will($this->returnValue((string) mfBepadoConfiguration::ARTICLE_STATE_NONE));

        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(0, $result);
    }

    public function testExportedArticleState()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase
            ->expects($this->once())
            ->method('getFieldData')
            ->will($this->returnValue((string) mfBepadoConfiguration::ARTICLE_STATE_EXPORTED));

        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(1, $result);
    }

    public function testImportedArticleState()
    {
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase
            ->expects($this->once())
            ->method('getFieldData')
            ->will($this->returnValue((string) mfBepadoConfiguration::ARTICLE_STATE_IMPORTED));

        $result = $this->helper->getArticleBepadoState($this->oxArticle);

        $this->assertEquals(2, $result);
    }

    public function testOnSaveArticleExtendDeleteExported()
    {
        $this->createBepadoStateObject();
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "0")));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase->expects($this->once())->method('delete');

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnSaveArticleExtendNothingShouldhappenWhenNotFoundAndStateFalse()
    {
        $this->createBepadoStateObject();
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "0")));
        $this->oxBase->expects($this->at(0))->method('isLoaded')->will($this->returnValue(false));
        $this->oxBase->expects($this->at(1))->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase->expects($this->never())->method('assign');
        $this->oxBase->expects($this->never())->method('save');

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnSaveArticleExtendNothingShouldHapenWhenFoundAndStateTrue()
    {
        $this->createBepadoStateObject();
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "1")));
        $this->oxBase->expects($this->at(0))->method('isLoaded')->will($this->returnValue(false));
        $this->oxBase->expects($this->at(1))->method('isLoaded')->will($this->returnValue(true));
        $this->oxBase->expects($this->never())->method('delete');

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnSaveArticleExtendSaveNewEntry()
    {
        $this->createBepadoStateObject();
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue(array('export_to_bepado' => "1")));
        $this->oxBase->expects($this->any())->method('isLoaded')->will($this->returnValue(false));
        $this->oxBase
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo(array(
                'p_source_id' => 'test-id',
                'OXID'        => 'test-id',
                'shop_id'     => '_self_',
                'state'       => 1
            )));
        $this->oxBase->expects($this->once())->method('save');

        $this->helper->onSaveArticleExtend('test-id');
    }

    public function testOnArticleDeleteWithUnknownArticle()
    {
        $resultSet = $this->getMockBuilder('object_ResultSet')->disableOriginalConstructor()->getMock();
        $resultSet->expects($this->once())->method('getArray')->will($this->returnValue(array()));

        $this->oxDb
            ->expects($this->once())
            ->method('execute')
            ->with($this->equalTo("SELECT * FROM bepado_product_state WHERE `OXID` LIKE 'test-id'"))
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
            ->with($this->equalTo("SELECT * FROM bepado_product_state WHERE `OXID` LIKE 'test-id'"))
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
        $this->oxBase->expects($this->any())->method('init')->with($this->equalTo('bepado_product_state'));
        $this->oxBase->expects($this->any())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->once())->method('isLoaded')->will($this->returnValue($value));
        if ($value) {
            $this->oxBase
                ->expects($this->once())
                ->method('getFieldData')
                ->with($this->equalTo('state'))
                ->will($this->returnValue(mfBepadoConfiguration::ARTICLE_STATE_EXPORTED));
        }
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

    protected function createBepadoStateObject()
    {
        $this->oxBase->expects($this->once())->method('init')->with($this->equalTo('bepado_product_state'));
        $this->oxBase
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'test-id', 'shop_id' => '_self_')))
            ->will($this->returnValue('some-sql'));
        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('some-sql'))
            ->will($this->returnValue('state-id'));
        $this->oxBase
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('state-id'))
            ->will($this->returnValue(true));
    }

    /**
     * @expectedException \Exception
     * @expectedMessage "Article is not managed for bepado. Neither exported to a remote shop nor imported."
     */
    public function testComputeToSDKProductThrowsExceptionWhenArticleIsNotManaged()
    {
        $this->oxBase->expects($this->once())->method('init')->with($this->equalTo('bepado_product_state'));
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase
            ->expects($this->once())
            ->method('getFieldData')
            ->with($this->equalTo('state'))
            ->will($this->returnValue(null));

        $this->helper->computeSdkProduct($this->oxArticle);
    }

    public function testComputeToSDKProduct()
    {
        $this->oxBase->expects($this->once())->method('init')->with($this->equalTo('bepado_product_state'));
        $this->oxBase->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxBase->expects($this->any())->method('getFieldData')->will($this->returnCallback(function() {
            $args = $args = func_get_args();
            $returnValues = array(
                'p_source_id' => 'source-id',
                'shop_id'     => 'shop-id',
                'state'       => 1
            );
            return $returnValues[$args[0]];
        }));
        $this->productConverter
            ->expects($this->once())
            ->method('fromShopToBepado')
            ->will($this->returnValue(new Product()));

        $product = $this->helper->computeSdkProduct($this->oxArticle);

        $this->assertInstanceOf(get_class(new Product()), $product);
        $this->assertEquals('source-id', $product->sourceId);
        $this->assertEquals('shop-id', $product->shopId);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxbase'           => $this->oxBase,
            'mf_sdk_helper'    => $this->sdkHelper,
            'mf_sdk_converter' => $this->productConverter,
        );
    }
}
