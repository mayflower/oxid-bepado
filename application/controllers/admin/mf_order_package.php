<?php

class mf_order_package extends mf_order_package_parent
{
    const BEPADO_PIC = 'application/out/img/bepado_b.png';

    public function render()
    {
        parent::render();

        $aOrders = $this->_aViewData['resultset'];

        foreach ($aOrders as $orderItem) {
            $orderItem->oxorder__importpic = new oxField(
                self::BEPADO_PIC,
                oxField::T_RAW
            );
        }

        return "mf_order_package.tpl";
    }
}
 