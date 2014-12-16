<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class VersionLayerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateWithCurrentShopConfig()
    {
        /** @var VersionLayerFactory $factory */
        $factory = oxNew('VersionLayerFactory');
        $verstionLayer = $factory->create();

        $this->assertInstanceOf('\VersionLayer490', $verstionLayer);
    }
}
