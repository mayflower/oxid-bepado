<?php

require_once __DIR__.'/../../BaseTestCase.php';

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_category_mainTest extends BaseTestCase
{
    protected $sdkHelper;
    protected $sdk;
    protected $sdkConfig;

    protected function setUp()
    {
        parent::prepareVersionLayerWithConfig();
        parent::setUp();

        /** @var oxCategory $oCategory */
        $oCategory = oxNew('oxCategory');
        $oCategory->setId('_testCatId');
        $oCategory->oxcategories__oxparentid = new oxField('oxrootid');
        $oCategory->save();

        $this->_oCategory = $oCategory;

        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->sdkConfig = new mfBepadoConfiguration();
        $this->sdkConfig->setApiEndpointUrl('test-endpoint');
        $this->sdkConfig->setApiKey('test-api-key');
        $this->sdkConfig->setSandboxMode(true);
        $this->sdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->sdkHelper->expects($this->any())->method('computeConfiguration')->will($this->returnValue($this->sdkConfig));
        $this->sdkHelper->expects($this->any())->method('instantiateSdk')->will($this->returnValue($this->sdk));
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        $this->cleanUpTable('oxcategories');
        oxDb::getDb()->execute("DELETE FROM oxcategories WHERE OXTITLE = 'Test category title for unit' ");

        parent::tearDown();
    }

    public function testRender()
    {
        modConfig::setRequestParameter("oxid", "testId");
        oxTestModules::addFunction("oxcategory", "isDerived", "{return true;}");

        $this->sdk
            ->expects($this->any())
            ->method('getCategories')
            ->will($this->returnValue(array(
                '/path/to/category1' => 'Category 1',
                '/path/to/category2' => 'Category 2',
            )));

        $oView = $this->getProxyClass("mf_category_main");
        // $oView->setVersionLayer($this->versionLayer);
        $result = $oView->render();

        $this->assertEquals('category_main.tpl', $result);
        $aViewData = $oView->getViewData();
        $this->assertTrue(isset($aViewData['googleCategories']));
        $this->assertTrue(isset($aViewData['bepardoCategory']));
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_helper' => $this->sdkHelper
        );
    }
}
