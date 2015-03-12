<?php

/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * This extension adds additional information to the packing list in the order section.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_order_package extends mf_order_package_parent
{
    const BEPADO_PIC = 'application/out/img/bepado_b.png';

    /**
     * An article, which is imported will get a bepado icon in the packing list.
     *
     * An extra template is used for that.
     *
     * @return string
     */
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
