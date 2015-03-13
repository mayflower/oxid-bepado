<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_export_mainTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_product_export_main();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_product_export_main.tpl', $sTemplate);
    }
}
