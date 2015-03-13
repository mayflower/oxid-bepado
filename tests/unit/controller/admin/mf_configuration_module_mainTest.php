<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_module_mainTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_configuration_module_main();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_configuration_module_main.tpl', $sTemplate);
    }
}
