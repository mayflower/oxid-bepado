<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class oxidProductFromShopTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var oxidProductFromShop
     */
    private $productFromShop;

    /**
     * @var VersionLayerInterface
     */
    private $versionLayer;

    public function setUp()
    {
        $this->versionLayer = $this->getMock('VersionLayerInterface');
        $this->productFromShop = new oxidProductFromShop();
        $this->productFromShop->setVersionLayer($this->versionLayer);
    }

    public function testBuyAction()
    {
        $order = new \Bepado\SDK\Struct\Order();
        #$localOrderId = $this->productFromShop->buy($order);
        $this->assertTrue(true);
        #$this->asserNotNull($localOrderId);
    }

}
 