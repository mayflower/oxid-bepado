<?php
use Bepado\SDK\SDK;
use Bepado\SDK\Struct\Product;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_bepado_oxarticle extends mf_bepado_oxarticle_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

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

    public function delete($oxId = null)
    {
        $config  = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($config);

        if ($this->productIsKnown($oxId)) {
            $sdk->recordDelete($oxId);
        }

        return parent::delete($oxId);
    }

    /**
     * Decider if an article marked as "export to bebado" or not.
     *
     * @return bool
     */
    public function readyForExportToBepado()
    {
        $id = $this->getId();
        /** @var oxBase $oBepadoProductState */
        $oBepadoProductState = oxNew('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $select = $oBepadoProductState->buildSelectString(array(
            'p_source_id' => $id,
            'shop_id'     => SDKConfig::SHOP_ID_LOCAL,
            'state'       => SDKConfig::ARTICLE_STATE_EXPORTED
        ));
        $id = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oBepadoProductState->load($id);

        return $oBepadoProductState->isLoaded();
    }

    /**
     * @return int
     */
    public function getState()
    {
        $id = $this->getId();
        /** @var oxBase $oBepadoProductState */
        $oBepadoProductState = oxNew('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $oBepadoProductState->load($id);

        $state = $oBepadoProductState->bepado_product_state__state->rawValue;

        if (!$state) {
            $state = SDKConfig::ARTICLE_STATE_NONE;
        }

        return $state;
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
        $result = $this->getVersionLayer()->getDb(true)->execute($sql);

        return count($result->getArray()) > 0;
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

    /**
     * @return bool
     */
    public function isImportedFromBepado()
    {
        if ($this->getState() != 2) {
            return false;
        }
        return true;
    }

    /**
     * @return Product
     */
    public function getSdkProduct()
    {
        /** @var mf_sdk_converter $oModuleSDKConverter */
        $oModuleSDKConverter = oxNew('mf_sdk_converter');

        $sdkProduct = $oModuleSDKConverter->toBepadoProduct($this);

        return $sdkProduct;
    }

    /**
     * @return bool|array
     * @throws Exception
     */
    public function checkProductWithBepardo()
    {
        $sdkProduct = $this->getSdkProduct();

        if (!$this->isImportedFromBepado()) {
            throw new Exception("Product is not imported from Bepado.");
        }

        $oState = oxNew('oxbase');
        $oState->init('bepado_product_state');
        $oState->load($this->getId());

        $sdkProduct->shopId = $oState->bepado_product_state__shop_id->rawValue;

        $config  = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($config);

        $result = null;

        try {
            $results = $sdk->checkProducts(array($sdkProduct));
            $result = $results[0];

        } catch (\Exception $e) {
            # var_dump($e);
        }

        return $result;
    }
}
 