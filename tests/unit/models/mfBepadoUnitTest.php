<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfBepadoUnitTest extends OxidTestCase
{
    public function tearDown()
    {
        $this->cleanUpTable('mfbepadounits');
    }

    public function testSetterGetterAndPersistence()
    {
        $oBepadoUnit = new mfBepadoUnit();
        $oBepadoUnit->setId('some-id');
        $oBepadoUnit->setBepadoKey('m');
        $oBepadoUnit->save();

        $result = $this->getDb()->execute("SELECT * FROM mfbepadounits WHERE OXID = 'some-id'");
        $expectedResult = array('some-id', 'm');

        $this->assertEquals($expectedResult, $result->fields);

        $oBepadoUnit = new mfBepadoUnit();
        $oBepadoUnit->load('some-id');
        $this->assertTrue($oBepadoUnit->isLoaded());
        $this->assertEquals('m', $oBepadoUnit->getBepadoKey());
    }

    public function testSetInvalidBepadoUnitKeySetNull()
    {
        $oBepadoUnit = new mfBepadoUnit();
        $oBepadoUnit->setBepadoKey('some');

        $this->assertNull($oBepadoUnit->getBepadoKey());
    }
}
