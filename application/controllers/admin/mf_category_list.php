<?php


class mf_category_list extends mf_category_list_parent
{
    public function render()
    {
        parent::render();

        $bepadoCategories = oxNew('oxlist');
        $bepadoCategories->init('oxbase', 'bepado_categories');
        $bepadoCategories->getBaseObject();
        $bepadoCategories->getList();
        $bepadoCategories = $bepadoCategories->getArray();

        foreach ($this->_aViewData['mylist'] as $key => $value) {
            // the bepado categories got an extra field (catnid) for the mapped oxid categories
            $matchingCategory = array_filter($bepadoCategories, function($category) use ($key) {
                return $category->getFieldData('bepado_categories__catnid') == $key;
            });
            if (count($matchingCategory) === 1) {
                $category = array_shift($matchingCategory);
                $value->oxcategories__bepadocategory = new oxField(
                    $category->bepado_categories__title->rawValue,
                    oxField::T_TEXT
                );
            }
        }

        return 'mf_category_list.tpl';
    }
}
