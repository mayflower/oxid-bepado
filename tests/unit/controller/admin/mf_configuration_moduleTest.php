<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_moduleTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_configuration_module();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_configuration_module.tpl', $sTemplate);
    }
}
