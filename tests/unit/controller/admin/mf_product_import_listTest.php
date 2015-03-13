<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_import_listTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_product_import_list();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_product_import_list.tpl', $sTemplate);
    }
}
