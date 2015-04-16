<?php

use Bepado\SDK\Struct\Shop;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
abstract class mf_product_admin_listTest extends BaseTestCase
{
    /**
     * @var mf_product_admin_list
     */
    protected $oView;

    protected $mfSdkHelper;
    protected $sdk;
    protected $oxArticle;

    public function setUp()
    {
        parent::setUp();
        parent::prepareVersionLayerWithConfig();

        $this->mfSdkHelper = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->sdk = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->mfSdkHelper
            ->expects($this->any())
            ->method('instantiateSdk')
            ->will($this->returnValue($this->sdk));
        $this->oxArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
    }

    public function testGetShopById()
    {
        $expectedShop = new Shop();
        $expectedShop->id = 'some-id';
        $expectedShop->name = 'some-name';
        $expectedShop->url = 'http://www.some-shop.de';

        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with($this->equalTo('some-id'))
            ->will($this->returnValue($expectedShop));

        $actualShop = $this->oView->getShopById('some-id');

        $this->assertEquals($expectedShop, $actualShop);
    }

    public function testGetShopByIdWontAskTwice()
    {

        $expectedShop = new Shop();
        $expectedShop->id = 'some-id';
        $expectedShop->name = 'some-name';
        $expectedShop->url = 'http://www.some-shop.de';

        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with($this->equalTo('some-id'))
            ->will($this->returnValue($expectedShop));

        $this->oView->getShopById('some-id');
        $this->oView->getShopById('some-id');
    }

    public function testGetShopByIdReplacesNullByUnknown()
    {
        $givenShop = new Shop();
        $givenShop->id = 'some-id';
        $givenShop->name = null;
        $givenShop->url = 'http://www.some-shop.de';

        $this->sdk
            ->expects($this->once())
            ->method('getShop')
            ->with($this->equalTo('some-id'))
            ->will($this->returnValue($givenShop));
        $expectedShop = clone $givenShop;
        $expectedShop->name = 'Unknown';

        $actualShop = $this->oView->getShopById('some-id');
        $this->assertEquals($expectedShop, $actualShop);
    }

    public function testRenderWithFilterForState()
    {
        $oMfBepadoProductExported = new mfBepadoProduct();
        $oMfBepadoProductExported->setState(mfBepadoProduct::PRODUCT_STATE_EXPORTED);
        $oMfBepadoProductExported->setProductSourceId('exported-id');

        $oMfBepadoProductImported = new mfBepadoProduct();
        $oMfBepadoProductImported->setState(mfBepadoProduct::PRODUCT_STATE_IMPORTED);
        $oMfBepadoProductImported->setProductSourceId('imported-id');

        $oList = new oxList();
        $oList->offsetSet("1", $oMfBepadoProductExported);
        $oList->offsetSet("2", $oMfBepadoProductImported);

        $sClassName = mfBepadoProduct::PRODUCT_STATE_EXPORTED === $this->getState() ? 'mf_product_export_list' : 'mf_product_import_list';

        $oView = $this->getMock($sClassName, array("getItemList"));
        $oView->expects($this->any())->method('getItemList')->will($this->returnValue($oList));
        $oView->render();
        $aViewData = $oView->getViewData();

        $myList = $aViewData['mylist']->getArray();
        $this->assertCount(1, $myList, 'There should be one item only in the list after the filter.');
        $actualItem = array_shift($myList);
        $expectedId = mfBepadoProduct::PRODUCT_STATE_EXPORTED === $this->getState() ? 'exported-id' : 'imported-id';
        $this->assertEquals($expectedId, $actualItem->getProductSourceId());
    }

    /**
     * For the filter test on render() method.
     *
     * @return string
     */
    abstract protected function getState();

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_helper'         => $this->mfSdkHelper,
            'sdk'                   => $this->sdk,
            'oxArticle'             => $this->oxArticle
        );
    }
}
