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
 * This extension adds additional information to the list of orders.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_list_order extends mf_list_order_parent
{
    const BEPADO_PIC = 'application/out/img/bepado.png';

    /**
     * A order list will get an bepado icon, when it contains
     * imported articles in its article list.
     *
     * @return string
     */
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
        }

        return $oList;
    }

    /**
     * Returns select query string
     *
     * @param object $oObject Object
     *
     * @return string
     */
    protected function _buildSelectString($oObject = null)
    {
        return 'select oxorderarticles.oxid, oxorder.oxid as oxorderid, max(oxorder.oxorderdate) as oxorderdate, oxorderarticles.oxartnum, sum( oxorderarticles.oxamount ) as oxorderamount, oxorderarticles.oxtitle, round( sum(oxorderarticles.oxbrutprice*oxorder.oxcurrate),2) as oxprice, oxorderarticles.imported from oxorderarticles left join oxorder on oxorder.oxid=oxorderarticles.oxorderid where 1 ';
    }
}
