<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_module_mainTest extends BaseTestCase
{
    protected $moduleHelper;
    protected $bepadoConfiguration;

    public function setUp()
    {
        parent::setUp();
        parent::prepareVersionLayerWithConfig();
        $this->bepadoConfiguration = $this->getMockBuilder('mfBepadoConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleHelper = $this->getMockBuilder('mf_module_helper')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRender()
    {
        $oView = new mf_configuration_module_main();
        $sTemplate = $oView->render();
        $viewData = $oView->getViewData();

        $this->assertNull($viewData['verified']);
        $this->assertEquals(array('A', 'B', 'C'), $viewData['available_purchaseGroups']);
        $this->assertInstanceOf('mfBepadoConfiguration', $viewData['edit']);

        $this->assertEquals('mf_configuration_module_main.tpl', $sTemplate);
    }

    public function testSaveVerified()
    {
        $editValues = array(
            'mfbepadoconfiguration__oxid' => 'shop-id',
            'mfbepadoconfiguration__sandboxmode' => '1',
            'mfbepadoconfiguration__shophintonarticledetails' => '0',
            'mfbepadoconfiguration__marketplacehintbasket' => '0',
            'mfbepadoconfiguration__apikey' => 'some-key',
            'mfbepadoconfiguration__purchasegroup' => 'A',
        );

        $this->moduleHelper
            ->expects($this->once())
            ->method('verifyAtSdk')
            ->will($this->returnValue(true));
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue($editValues));
        $this->bepadoConfiguration->expects($this->once())->method('assign')->with($this->equalTo($editValues));
        $this->bepadoConfiguration->expects($this->once())->method('save');

        $oView = new mf_configuration_module_main();
        $oView->setVersionLayer($this->versionLayer);
        $oView->save();
        $oView->render();
        $viewData = $oView->getViewData();

        $this->assertTrue($viewData['verified']);
    }

    public function testSaveNotVerified()
    {
        $editValues = array(
            'mfbepadoconfiguration__oxid' => 'shop-id',
            'mfbepadoconfiguration__sandboxmode' => '1',
            'mfbepadoconfiguration__shophintonarticledetails' => '0',
            'mfbepadoconfiguration__marketplacehintbasket' => '0',
            'mfbepadoconfiguration__apikey' => 'some-key',
            'mfbepadoconfiguration__purchasegroup' => 'A',
        );

        $this->moduleHelper
            ->expects($this->once())
            ->method('verifyAtSdk')
            ->will($this->returnValue(false));
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue($editValues));
        $this->bepadoConfiguration->expects($this->once())->method('assign')->with($this->equalTo($editValues));
        $this->bepadoConfiguration->expects($this->once())->method('save');

        $oView = new mf_configuration_module_main();
        $oView->setVersionLayer($this->versionLayer);
        $oView->save();
        $oView->render();
        $viewData = $oView->getViewData();

        $this->assertFalse($viewData['verified']);
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_module_helper'      => $this->moduleHelper,
            'mfBepadoConfiguration' => $this->bepadoConfiguration,
        );
    }
}
