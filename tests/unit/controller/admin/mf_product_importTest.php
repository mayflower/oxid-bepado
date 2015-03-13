<?php

/**
 * This controller will render the view of the imported products admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_importTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_product_import();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_product_import.tpl', $sTemplate);
    }
}
