<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_export_listTest extends mf_product_admin_listTest
{
    public function setUp()
    {
        parent::setUp();

        $this->oView = new mf_product_export_list();
        $this->oView->setVersionLayer($this->versionLayer);
    }

    public function testRender()
    {
        $this->assertEquals('mf_product_export_list.tpl', $this->oView->render());
    }

    /**
     * For the filter test on render() method.
     *
     * @return string
     */
    protected function getState()
    {
        return mfBepadoProduct::PRODUCT_STATE_EXPORTED;
    }
}
