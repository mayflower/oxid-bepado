<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_unit_mainTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_configuration_unit_main();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_configuration_unit_main.tpl', $sTemplate);
    }
}
