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
 * This extension add additional information to the article list of an order.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_order_article extends mf_order_article_parent
{
    const BEPADO_PIC = 'application/out/img/bepado.png';

    /**
     * The article list of the order will get a bepado icon for imported articles.
     *
     * @return string
     */
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
