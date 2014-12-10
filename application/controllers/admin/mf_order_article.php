<?php

class mf_order_article extends mf_order_article_parent
{
    const BEPADO_PIC = 'application/out/src/img/bepado.png';

    public function render()
    {
        $oList = parent::render();

        if ($oOrder = $this->getEditObject()) {
            $oOrder->oxorder__importpic = new oxField(
                self::BEPADO_PIC,
                oxField::T_RAW
            );

            $this->_aViewData["edit"] = $oOrder;
        }

        return $oList;
    }
}
 