<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_article_list extends mf_article_list_parent
{
    public function getItemList()
    {
        $oList = parent::getItemList();

        foreach ($this->_oList as $key => $listItem) {
            $listItem->oxarticles__exporttobepado = new oxField(
                $listItem->readyForExportToBepado() ? 1 : 0,
                oxField::T_RAW
            );
        }

        return $oList;
    }



}
 