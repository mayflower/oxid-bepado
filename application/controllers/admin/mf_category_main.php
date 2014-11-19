<?php


class mf_category_main extends mf_category_main_parent
{
    public function render()
    {
        $aCategories = $this->getGoogleCategories();

        $soxId = parent::getEditObjectId();
        if (!isset($aCategories)) {
            $aCategories = [];
        }

        $oCat = oxNew('oxbase');
        $oCat->init('bepado_categories');
        if ($soxId != "-1" && isset($soxId)){
            try {
                $oCat->load($soxId);
            } catch (\Exception $e) {
                // do nothing
            }
        }

        $this->_aViewData['googleCategories'] = $aCategories;
        $this->_aViewData['bepardoCategory'] = $oCat;

        return parent::render();
    }

    public function save()
    {
        parent::save();
        $myConfig = parent::getConfig();

        $aParams = parent::_parseRequestParametersForSave(
            $myConfig->getRequestParameter("mf_editval")
        );
        $oCat = oxNew('oxbase');
        $oCat->init('bepado_categories');
        $oCat->assign($aParams);
        $oCat->save();

    }

    private function getGoogleCategories() {

        return file(__DIR__."/../../install/taxonomy.de_DE.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
