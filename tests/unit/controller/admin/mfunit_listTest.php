<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfunit_listTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mfunit_list();
        $sTemplate = $oView->render();

        $this->assertEquals('mfunit_list.tpl', $sTemplate);
    }
}
