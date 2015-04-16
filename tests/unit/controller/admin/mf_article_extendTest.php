<?php

require_once __DIR__.'/../../BaseTestCase.php';

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_article_extendTest extends BaseTestCase
{
    /**
     * @var mf_article_extend
     */
    protected $oView;
    protected $oxArticle;
    protected $mfSdkArticleHelper;

    public function setUp()
    {
        parent::setUp();
        parent::prepareVersionLayerWithConfig();

        $this->oxArticle = $this->getMockBuilder('mf_bepado_oxarticle')->disableOriginalConstructor()->getMock();
        $this->oxArticle->expects($this->any())
            ->method('getVersionLayer')
            ->will($this->returnValue($this->versionLayer));
        $this->mfSdkArticleHelper = $this->getMockBuilder('mf_sdk_article_helper')->disableOriginalConstructor()->getMock();

        $this->oView = new mf_article_extend();
        $this->oView->setVersionLayer($this->versionLayer);
    }

    public function testRenderWithStateNone()
    {
        $this->mfSdkArticleHelper->expects($this->once())->method('getArticleBepadoState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_NONE));
        $this->oView->render();
        $aViewData = $this->oView->getViewData();

        $this->assertEquals(mfBepadoProduct::PRODUCT_STATE_NONE, $aViewData['export_to_bepado']);
        $this->assertEquals(1, $aViewData['no_bepado_import']);
    }

    public function testRenderWithStateExported()
    {
        $this->mfSdkArticleHelper->expects($this->once())->method('getArticleBepadoState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_EXPORTED));
        $this->oView->render();
        $aViewData = $this->oView->getViewData();

        $this->assertEquals(mfBepadoProduct::PRODUCT_STATE_EXPORTED, $aViewData['export_to_bepado']);
        $this->assertEquals(1, $aViewData['no_bepado_import']);
    }

    public function testRenderWithStateImported()
    {
        $this->mfSdkArticleHelper->expects($this->once())->method('getArticleBepadoState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_IMPORTED));
        $this->oView->render();
        $aViewData = $this->oView->getViewData();

        $this->assertFalse(isset($aViewData['export_to_bepado']));
        $this->assertFalse(isset($aViewData['no_bepado_import']));
    }

    public function testSaveWithNoExceptionWhileMarkingAsExported()
    {
        $this->oView->save();
        $aViewData = $this->oView->getViewData();

        $this->assertFalse(isset($aViewData['errorExportingArticle']));
        $this->assertFalse(isset($aViewData['errorMessage']));
    }

    public function testSaveWithExceptionWhileMarkingAsExported()
    {
        $this->markTestSkipped('Can`t mock parent class atm.');
        $exception = new \Bepado\SDK\Exception\VerificationFailedException('some message');
        $this->mfSdkArticleHelper
            ->expects($this->once())
            ->method('onArticleSave')
            ->will($this->throwException($exception));

        $this->oView->save();
        $aViewData = $this->oView->getViewData();

        $this->assertTrue($aViewData['errorExportingArticle']);
        $this->assertEquals('', $aViewData['errorMessage']);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxArticle'             => $this->oxArticle,
            'mf_sdk_article_helper' => $this->mfSdkArticleHelper,
        );
    }
}
