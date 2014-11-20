<?php


class mf_category_list extends mf_category_list_parent
{
    public function render()
    {
        parent::render();

        $oCat = oxNew('oxlist');
        $oCat->init('oxbase', 'bepado_categories');
        $oCat->getBaseObject();
        $oCat->getList();
        $oCat = $oCat->getArray();

        foreach ($this->_aViewData['mylist'] as $key => $value) {
            if (array_key_exists($key, $oCat)) {
                $value->oxcategories__bepadocategory = new oxField(
                    $oCat[$key]->bepado_categories__title->rawValue,
                    oxField::T_TEXT
                );
            }
        }

        return 'mf_category_list.tpl';
    }
}
