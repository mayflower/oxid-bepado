<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_import_mainTest extends BaseTestCase
{
    protected $oArticleHelper;
    protected $oVendorList;

    /**
     * @var mf_product_import_main
     */
    protected $oView;
    protected $oArticle;
    protected $mfBepadoProduct;

    public function setUp()
    {
        parent::setup();
        parent::prepareVersionLayerWithConfig();
        $this->oArticleHelper = $this->getMockBuilder('mf_sdk_article_helper')->disableOriginalConstructor()->getMock();
        $this->oVendorList = $this->getMockBuilder('oxVendorList')->disableOriginalConstructor()->getMock();
        $this->oArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
        $this->mfBepadoProduct = new mfBepadoProduct();

        $this->oView = new mf_product_import_main();
        $this->oView->setVersionLayer($this->versionLayer);
    }

    public function testRender()
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

        $this->assertEquals('mf_product_import_main.tpl', $sTemplate);
    }

    public function testSave()
    {
        $aEditValues = array(
            'oxArticle'       => array(
                'oxarticles__'
            ),
            'mfBepadoProduct' => array(),
        );
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue($aEditValues));

        $this->oArticle
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo($aEditValues['oxArticle']))
            ;
        $this->oArticle->expects($this->once())->method('save');

        $this->oView->save();
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_article_helper' => $this->oArticleHelper,
            'oxVendorList'          => $this->oVendorList,
            'oxArticle'             => $this->oArticle,
            'mfBepadoProduct'       => $this->mfBepadoProduct,
        );
    }
}
