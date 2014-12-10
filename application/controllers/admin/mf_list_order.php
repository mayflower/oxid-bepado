<?php

class mf_list_order extends mf_list_order_parent
{
    const BEPADO_PIC = 'application/out/src/img/bepado.png';

    public function render()
    {
        $oList = parent::render();

        foreach ($this->_oList as $listItem) {

            if ($listItem->oxorder__imported->rawValue == 1) {
                $listItem->oxorder__importpic = new oxField(
                    self::BEPADO_PIC,
                    oxField::T_RAW
                );
            }
            if ($listItem->oxorder__oxtitle->value == 'Gadget') {
                $listItem->oxorder__importpic = new oxField(
                    self::BEPADO_PIC,
                    oxField::T_RAW
                );
            }
        }

        return $oList;
    }
}
 