<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfBepadoProductTest extends OxidTestCase
{
    public function tearDown()
    {
        $this->cleanUpTable('mfbepadoproducts');
    }

    public function testSetter()
    {
        $oBepadoProduct = new mfBepadoProduct();
        $oBepadoProduct->setId('_prod-id');
        $oBepadoProduct
            ->setState(mfBepadoProduct::PRODUCT_STATE_EXPORTED)
            ->setShopId('shop-id')
            ->setProductSourceId('product-source-id')
            ;
        $oBepadoProduct->save();

        $result = $this->getDb()->execute("SELECT * FROM mfbepadoproducts WHERE OXID ='_prod-id'");
        $actualFields = $result->fields;
        $expectedFields = array('_prod-id', 'product-source-id', 'shop-id', '1');

        $this->assertEquals($expectedFields, $actualFields);
    }

    public function testGetter()
    {
        $this->getDb()->execute("INSERT INTO mfbepadoproducts (`OXID`, `p_source_id`, `shop_Id`, `state`) VALUES ('_prod-id', 'product-source-id', 'shop-id', '1')");

        $oBepadoProduct = new mfBepadoProduct();
        $oBepadoProduct->load('_prod-id');

        $this->assertTrue($oBepadoProduct->isLoaded());
        $this->assertEquals(1, $oBepadoProduct->getState());
        $this->assertEquals('shop-id', $oBepadoProduct->getShopId());
        $this->assertEquals('product-source-id', $oBepadoProduct->getProductSourceId());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Product state 4 is not supported. Use one of 1,2.
     */
    public function testSetFalsyStateThrowsException()
    {
        $oBepadoProduct = new mfBepadoProduct();
        $oBepadoProduct->setState(4);
    }

    public function testGetStateReturnStateNoneForNull()
    {
        $oBepadoProduct = new mfBepadoProduct();
        $this->assertEquals(mfBepadoProduct::PRODUCT_STATE_NONE, $oBepadoProduct->getState());
    }
}
