<?php

/**
 * This controller will render the view of the unit mapping admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_unitTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mf_configuration_unit();
        $sTemplate = $oView->render();

        $this->assertEquals('mf_configuration_unit.tpl', $sTemplate);
    }
}
