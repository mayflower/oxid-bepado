<?php

class mf_list_order extends mf_list_order_parent
{
    const BEPADO_PIC = 'application/out/img/bepado.png';

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
 