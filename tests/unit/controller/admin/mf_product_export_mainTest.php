<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_export_mainTest extends BaseTestCase
{
    /**
     * @var mf_product_export_main
     */
    protected $oView;
    protected $oArticleHelper;
    protected $oArticle;
    protected $mfBepadoProduct;
    protected $oxDb;
    protected $oxArticleList;
    protected $oxList;
    protected $oxArticle;

    public function setUp()
    {
        parent::setUp();
        parent::prepareVersionLayerWithConfig();
        $this->oArticleHelper = $this->getMockBuilder('mf_sdk_article_helper')->disableOriginalConstructor()->getMock();
        $this->oArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
        $this->oxArticleList = $this->getMockBuilder('oxArticleList')->disableOriginalConstructor()->getMock();
        $this->oxList = $this->getMockBuilder('oxList')->disableOriginalConstructor()->getMock();
        $this->mfBepadoProduct = new mfBepadoProduct();
        $this->oxArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
        $this->oxArticleList->expects($this->any())->method('getBaseObject')->will($this->returnValue($this->oxArticle));
        $this->oxList->expects($this->any())->method('getBaseObject')->will($this->returnValue($this->mfBepadoProduct));

        $this->oView = new mf_product_export_main();
        $this->oView->setVersionLayer($this->versionLayer);
    }

    public function testRenderProductExportedMain()
    {
        $oField = new oxField();
        $oField->setValue('some value');
        $this->oArticle->expects($this->once())->method('getLongDescription')->will($this->returnValue($oField));

        $sTemplate = $this->oView->render();
        $aViewData = $this->oView->getViewData();

        $this->assertInstanceOf('mfBepadoProduct', $aViewData['edit']->mfBepadoProduct);
        $this->assertInstanceOf('oxArticle', $aViewData['edit']->oxArticle);
        $this->assertInstanceOf('mf_sdk_article_helper', $aViewData['edit']->oArticleHelper);
        $this->assertNotNull($aViewData['editor']);
        $this->assertEquals('mf_product_export_main.tpl', $sTemplate);
        $this->assertTrue($aViewData['updatelist']);
    }

    public function testProductExportedMainGetArticlesToExport()
    {

        $expectedClass = new stdClass();
        $expectedClass->oxarticles__oxtitle = 'bla';

        $this->oxArticleList
            ->expects($this->once())
            ->method('getArray')
            ->will($this->returnValue(array('some-id' => $expectedClass, 'exported-id' => new stdClass())));
        $this->oxList
            ->expects($this->once())
            ->method('getArray')
            ->will($this->returnValue(array('exported-id' => 'blub')));

        $expectedClass->pwrsearchval = 'bla';
        $expectedResult = array('some-id' => $expectedClass);
        $actualResult = $this->oView->getArticlesToExport();
        $this->assertEquals($expectedResult, $actualResult);
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_article_helper' => $this->oArticleHelper,
            'oxArticle'             => $this->oArticle,
            'mfBepadoProduct'       => $this->mfBepadoProduct,
            'oxArticleList'         => $this->oxArticleList,
            'oxList'                => $this->oxList,
        );
    }
}
