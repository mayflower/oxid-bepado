<?php


class mf_category_main extends mf_category_main_parent
{
    private $_oModuleSdkHelper;

    public function render()
    {
        $sdkConfig = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($sdkConfig);
        $categories = $sdk->getCategories();

        // placeholder till functionality above has results
        $categories = ['home', 'garden', 'hobby', 'work'];

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
}
