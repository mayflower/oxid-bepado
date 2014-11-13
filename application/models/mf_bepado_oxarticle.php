<?php
use Bepado\SDK\SDK;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_bepado_oxarticle extends mf_bepado_oxarticle_parent
{
    const FIELDNAME_BEPADO_EXPORT = 'exporttobepado';

    /**
     * @var mf_sdk_helper
     */
    protected $_oModuleSdkHelper;

    /**
     * @var mf_sdk_converter
     */
    protected $_oProductConverter;

    /**
     * Does the sdk work when saving an oxid article.
     */
    public function save()
    {
        $return = parent::save();

        $config  = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($config);
        $sdkProduct = new \Bepado\SDK\Struct\Verificator\Product(); # todo fix when converter is ready $this->getSdkProduktConverter();
        if ($this->readyForExportToBepado()) {
            // todo look for existing products in the sdk
            $sdk->recordUpdate($sdkProduct);
        } else {
            $sdk->recordInsert($sdkProduct);
        }

        return $return;
    }

    /**
     * Decider if an article marked as "export to bebado" or not.
     *
     * @return bool
     */
    public function readyForExportToBepado()
    {
        return 1 === $this->getFieldData(self::FIELDNAME_BEPADO_EXPORT);
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

    private function getSdkProduktConverter()
    {
        if ($this->_oProductConverter === null) {
            $this->_oModuleSdkHelper = oxNew('mf_sdk_converter');
        }


    }
}
 