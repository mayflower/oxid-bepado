<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_article_list extends mf_article_list_parent
{
    const EXPORT_PIC = 'application/out/img/bepado_out.png';

    const IMPORT_PIC = 'application/out/img/bepado_in.png';

    public function getItemList()
    {
        $oList = parent::getItemList();

        foreach ($this->_oList as $key => $listItem) {

            if ($listItem->getState() == 1) {
                $listItem->oxarticles__state = new oxField(
                    self::EXPORT_PIC,
                    oxField::T_RAW
                );
            } elseif ($listItem->getState() == 2) {
                $listItem->oxarticles__state = new oxField(
                    self::IMPORT_PIC,
                    oxField::T_RAW
                );
            }
        }

        return $oList;
    }



}
 