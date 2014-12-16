<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class VersionLayerTest extends PHPUnit_Framework_TestCase {
    private $versionLayers = array(
        'VersionLayer460' => false,
        'VersionLayer470' => false,
        'VersionLayer490' => true,
        'VersionLayer500' => false,
    );

    private $expectedMethods = array(
        'getBasket'          => array('type' => 'class', 'value' => 'oxBasket'),
        'getSession'         => array('type' => 'class', 'value' => 'oxSession'),
        'getConfig'          => array('type' => 'class', 'value' => 'oxConfig'),
        'getDb'              => array('type' => 'class', 'value' => 'oxLegacyDb'),
        'getDeliverySetList' => array('type' => 'class', 'value' => 'oxDeliverySetList'),
        'getUtils'           => array('type' => 'class', 'value' => 'oxUtils'),
        'getRequestParam'    => array('type' => 'class', 'value' => 'VersionLayerTest'),
        'getLang'            => array('type' => 'class', 'value' => 'oxLang'),
        'getUtilsServer'     => array('type' => 'class', 'value' => 'oxUtilsServer'),
        'getUtilsUrl'        => array('type' => 'class', 'value' => 'oxUtilsUrl'),
        'getUtilsView'       => array('type' => 'class', 'value' => 'oxUtilsView'),
        'getUtilsObject'     => array('type' => 'class', 'value' => 'oxUtilsObject'),
        'getUtilsDate'       => array('type' => 'class', 'value' => 'oxUtilsDate'),
        'getUtilsString'     => array('type' => 'class', 'value' => 'oxUtilsString'),
        'getUtilsFile'       => array('type' => 'class', 'value' => 'oxUtilsFile'),
        'getUtilsPic'        => array('type' => 'class', 'value' => 'oxUtilsPic'),
        'getUtilsCount'      => array('type' => 'class', 'value' => 'oxUtilsCount'),
    );

    /**
     * @dataProvider getValues
     *
     * @param string $versionLayer
     * @param string $method
     * @param string $expectationType
     * @param string $expectedValue
     * @param bool   $testIt
     */
    public function testVersionLayer($versionLayer, $method, $expectationType, $expectedValue, $testIt)
    {
        $class = oxNew((string)$versionLayer);

        $this->assertTrue(method_exists($class, $method));

        if (!$testIt) {
            return;
        }

        if ('getRequestParam' === $method) {
            $actualValue = $class->{$method}('some-param');
        } else {
            $actualValue = $class->{$method}();
        }


        if ('class' === $expectationType) {
            $this->assertEquals($expectedValue, get_class($actualValue));
        }
    }

    /**
     * @dataProvider getVersionLayer
     *
     * @param $versionLayer
     * @param $testIt
     */
    public function testCreateNew($versionLayer, $testIt)
    {
        $class = oxNew((string)$versionLayer);

        $this->assertTrue(method_exists($class, 'createNewObject'));

        if (!$testIt) {
            return;
        }

        $result = $class->createNewObject('VersionLayerFactory');
        $this->assertInstanceOf('\VersionLayerFactory', $result);
    }

    public function getValues()
    {
        $result = array();

        foreach ($this->versionLayers as $versionLayer => $test) {
            foreach ($this->expectedMethods as $method => $expectation) {
                $result[] = array($versionLayer, $method, $expectation['type'], $expectation['value'], $test);
            }
        }

        return $result;
    }

    public function getVersionLayer()
    {
        $result = array();

        foreach ($this->versionLayers as $versionLayer => $testIt) {
            $result[] = array($versionLayer, $testIt);
        }

        return $result;
    }
}
