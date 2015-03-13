<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_import_mainTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_product_import_main();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_product_import_main.tpl', $sTemplate);
    }
}
