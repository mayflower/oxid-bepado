<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfunit_mainTest extends BaseTestCase
{
    protected $oxLang;
    protected $oxDb;
    protected $bepadoUnit;

    public function setUp()
    {
        parent::setUp();
        parent::prepareVersionLayerWithConfig();

        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->oxLang = $this->getMockBuilder('oxLang')->disableOriginalConstructor()->getMock();

        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
        $this->versionLayer->expects($this->any())->method('getLang')->will($this->returnValue($this->oxLang));

        $this->oxLang
            ->expects($this->any())
            ->method('getSimilarByKey')
            ->with($this->equalTo('_UNIT_'), $this->equalTo(null), $this->equalTo(false))
            ->will($this->returnValue(array(
                '_UNIT_A' => 'A',
                '_UNIT_B' => 'B',
                '_UNIT_C' => 'C',
            )));
        $this->oxDb
            ->expects($this->any())
            ->method('getAll')
            ->will($this->returnCallback(function ($sql) {
                if (preg_match('/OXID/', $sql)) {
                    return array(
                        array('_UNIT_A'),
                    );
                }
                if (preg_match('/BEPADOUNITKEY/', $sql)) {
                    return array(
                        array('m'),
                    );
                }
            }));

        $this->bepadoUnit = $this->getMockBuilder('mfBepadoUnit')->disableOriginalConstructor()->getMock();
    }

    public function testRender()
    {
        $oView = new mfunit_main();
        $sTemplate = $oView->render();
        $viewData = $oView->getViewData();

        $this->assertEquals('mfunit_main.tpl', $sTemplate);
        $this->assertInstanceOf('mfBepadoUnit', $viewData['edit']);
    }

    public function testSave()
    {
        $editValues = array(
            'mfbepadounits__oxid' => 'unit',
            'mfbepadounits__bepadounitkey' => 'b-unit',
        );
        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('editval'))
            ->will($this->returnValue($editValues));
        $this->bepadoUnit
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo($editValues));

        $oView = new mfunit_main();
        $oView->setVersionLayer($this->versionLayer);
        $oView->save();
    }

    public function testAvailableOxidUnits()
    {
        $oView = new mfunit_main();
        $oView->setVersionLayer($this->versionLayer);
        $actualUnits = $oView->computeAvailableOxidUnits(new mfBepadoUnit());
        $expectedUnit = array(
            '_UNIT_B' => 'B',
            '_UNIT_C' => 'C',
        );

        $this->assertEquals($expectedUnit, $actualUnits);
    }

    public function testAvailableOxidUnitsWithAdd()
    {
        $oView = new mfunit_main();
        $oView->setVersionLayer($this->versionLayer);
        $oBepadoUnit = new mfBepadoUnit();
        $oBepadoUnit->setId('_UNIT_A');
        $actualUnits = $oView->computeAvailableOxidUnits($oBepadoUnit);
        $expectedUnit = array(
            '_UNIT_A' => 'A',
            '_UNIT_B' => 'B',
            '_UNIT_C' => 'C',
        );

        $this->assertEquals($expectedUnit, $actualUnits);
    }

    public function testAvailableBepadoUnits()
    {
        $oView = new mfunit_main();
        $oView->setVersionLayer($this->versionLayer);
        $actualUnits = $oView->computeAvailableBepadoUnits(new mfBepadoUnit());
        $expectedUnit = array(
            'kg' => 'kg',
            'g' => 'g',
            'l' => 'l',
            'ml' => 'ml',
            'cm' => 'cm',
            'mm' => 'mm',
            'm^2' => 'm^2',
            'm^3' => 'm^3',
            'piece' => 'piece',
        );

        $this->assertEquals($expectedUnit, $actualUnits);
    }

    public function testAvailableBepadoUnitsWithAdd()
    {
        $oView = new mfunit_main();
        $oView->setVersionLayer($this->versionLayer);
        $oBepadoUnit = new mfBepadoUnit();
        $oBepadoUnit->setBepadoKey('m');
        $actualUnits = $oView->computeAvailableBepadoUnits($oBepadoUnit);
        $expectedUnit = array(
            'kg' => 'kg',
            'g' => 'g',
            'l' => 'l',
            'ml' => 'ml',
            'cm' => 'cm',
            'mm' => 'mm',
            'm' => 'm',
            'm^2' => 'm^2',
            'm^3' => 'm^3',
            'piece' => 'piece',
        );

        $this->assertEquals($expectedUnit, $actualUnits);
    }

    protected function getObjectMapping()
    {
        return array('mfBepadoUnit' => $this->bepadoUnit);
    }
}
