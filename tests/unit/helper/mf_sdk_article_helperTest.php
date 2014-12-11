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
        $this->oxBase->expects($this->once())->method('getFieldData')->will($this->returnValue(SDKConfig::ARTICLE_STATE_EXPORTED));

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
        $this->oxBase->expects($this->once())->method('getFieldData')->will($this->returnValue(SDKConfig::ARTICLE_STATE_IMPORTED));

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
        $this->oxBase->expects($this->once())->method('getFieldData')->will($this->returnValue(SDKConfig::ARTICLE_STATE_IMPORTED));

        $result = $this->helper->isOrderArticleImported($this->oxOrderArticle);

        $this->assertTrue($result);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxbase' => $this->oxBase,
        );
    }
}
