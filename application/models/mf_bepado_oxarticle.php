<?php
use Bepado\SDK\SDK;
use Bepado\SDK\Struct\Product;

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
        $oxProductId = $this->getFieldData('oxid');

        if ($this->readyForExportToBepado() && $this->productIsKnown($oxProductId)) {
            $sdk->recordUpdate($oxProductId);
        } elseif (!$this->readyForExportToBepado() && $this->productIsKnown($oxProductId)) {
            $sdk->recordDelete($oxProductId);
        } elseif ($this->readyForExportToBepado() && !$this->productIsKnown($oxProductId)) {
            $sdk->recordInsert($oxProductId);
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
        return "1" === $this->getFieldData(self::FIELDNAME_BEPADO_EXPORT);
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

    private function productIsKnown($oxProductId)
    {
        $sql = "SELECT * FROM bepado_product WHERE `p_source_id` LIKE '" . $oxProductId."'";
        $result = oxDb::getDb(true)->execute($sql);

        return count($result->getArray()) > 0;
    }
}
 