<?php

/**
 * This controller will render the view of the unit mapping admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfunitTest extends OxidTestCase
{
    public function testRender()
    {
        $oView = new mfunit();
        $sTemplate = $oView->render();

        $this->assertEquals('mfunit.tpl', $sTemplate);
    }
}
