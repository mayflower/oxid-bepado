<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_exportTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_product_export();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_product_export.tpl', $sTemplate);
    }
}
