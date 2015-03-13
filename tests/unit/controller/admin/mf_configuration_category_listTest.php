<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_category_listTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_configuration_category_list();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_configuration_category_list.tpl', $sTemplate);
    }
}
