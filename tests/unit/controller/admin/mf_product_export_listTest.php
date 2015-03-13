<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_export_listTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_product_export_list();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_product_export_list.tpl', $sTemplate);
    }
}
