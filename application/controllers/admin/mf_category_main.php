<?php


class mf_category_main extends mf_category_main_parent
{
    private $_oModuleSdkHelper;

    public function render()
    {
        $categories = $this->getGoogleCategories();

        if (!isset($categories)) {
            $categories = [];
        }

        $this->_aViewData['bepadoCategories'] = $categories;


        return parent::render();
    }

    /**
     * @return mf_sdk_helper
     */
    private function getSdkHelper()
    {
        if ($this->_oModuleSdkHelper === null) {
            $this->_oModuleSdkHelper = oxNew('mf_sdk_helper');
        }

        return $this->_oModuleSdkHelper;
    }

    private function getGoogleCategories() {
        return file(__DIR__."/../../install/taxonomy.de_DE.txt");
    }
}
