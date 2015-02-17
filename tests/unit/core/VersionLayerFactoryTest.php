<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class VersionLayerFactoryTest extends OxidTestCase
{

    /**
     * @var VersionLayerFactory
     */
    protected $factory;

    public function setUp()
    {
        parent::setUp();
        $this->factory = new VersionLayerFactory();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testCreateWithCurrentShopConfig()
    {
        $versionLayer = $this->factory->create();

        $this->assertInstanceOf('\VersionLayer490', $versionLayer);
    }

    public function testOtherCase()
    {
        $oConfig = oxRegistry::getConfig();

        $oConfig->saveShopConfVar('str', 'sLayerClassFile', null, null, 'bepado');
        $oConfig->saveShopConfVar('str', 'sLayerClass', null, null, 'bepado');

        $versionLayer = $this->factory->create();
        $this->assertInstanceOf('\VersionLayer490', $versionLayer);
    }
}
