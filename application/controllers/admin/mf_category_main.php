<?php


class mf_category_main extends mf_category_main_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    public function render()
    {
        $aCategories = $this->getSdkCategories();

        $soxId = parent::getEditObjectId();
        if (!isset($aCategories)) {
            $aCategories = [];
        }

        $oCat = $this->getVersionLayer()->createNewObject('oxbase');
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

    /**
     * @return array
     */
    private function getSdkCategories()
    {
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $sdkConfig = $sdkHelper->createSdkConfigFromOxid();
        $sdk = $sdkHelper->instantiateSdk($sdkConfig);

        return $sdk->getCategories();
    }

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    private function getVersionLayer()
    {
        if (null == $this->_oVersionLayer) {
            /** @var VersionLayerFactory $factory */
            $factory = oxNew('VersionLayerFactory');
            $this->_oVersionLayer = $factory->create();
        }

        return $this->_oVersionLayer;
    }
}
