<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_category_mainTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_configuration_category_main();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_configuration_category_main.tpl', $sTemplate);
    }
}
