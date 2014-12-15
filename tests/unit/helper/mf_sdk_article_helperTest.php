<?php

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
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
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
            ->will($this->returnValue((string) SDKConfig::ARTICLE_STATE_EXPORTED));

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
            ->will($this->returnValue((string) SDKConfig::ARTICLE_STATE_IMPORTED));

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
            ->will($this->returnValue((string) SDKConfig::ARTICLE_STATE_IMPORTED));

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
            ->will($this->returnValue((string) SDKConfig::ARTICLE_STATE_NONE));

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
            ->will($this->returnValue((string) SDKConfig::ARTICLE_STATE_EXPORTED));

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
            ->will($this->returnValue((string) SDKConfig::ARTICLE_STATE_IMPORTED));

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

        $this->helper->onSaveArticleExtend('some-id');
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

        $this->helper->onSaveArticleExtend('some-id');
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

        $this->helper->onSaveArticleExtend('some-id');
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
                'p_source_id' => 'some-id',
                'OXID'        => 'some-id',
                'shop_id'     => '_self_',
                'state'       => 1
            )));
        $this->oxBase->expects($this->once())->method('save');

        $this->helper->onSaveArticleExtend('some-id');
    }

    public function createBepadoStateObject()
    {
        $this->oxBase->expects($this->once())->method('init')->with($this->equalTo('bepado_product_state'));
        $this->oxBase
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'some-id', 'shop_id' => '_self_')))
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

    protected function getObjectMapping()
    {
        return array(
            'oxbase' => $this->oxBase,
        );
    }
}
