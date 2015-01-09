<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_basketTest extends OxidTestCase
{
    public function testRender()
    {
        $basket = new mf_basket();

        $oBasket = $this->getMock('oxBasket', array('getPrice', 'getProductsCount', 'getBasketArticles'));
        $basket->setViewData(array('oxcmp_basket' => $oBasket));

        $this->assertEquals('page/checkout/basket.tpl', $basket->render());
    }
}
