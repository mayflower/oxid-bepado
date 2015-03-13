<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_categoryTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_configuration_category();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_configuration_category.tpl', $sTemplate);
    }
}
