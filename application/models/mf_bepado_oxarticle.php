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
     * Does the sdk work when saving an oxid article.
     */
    public function save()
    {
        $return = parent::save();

        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $config  = $helper->createSdkConfigFromOxid();
        $sdk = $helper->instantiateSdk($config);
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
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $config  = $helper->createSdkConfigFromOxid();
        /** @var SDK $sdk */
        $sdk = $helper->instantiateSdk($config);

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
     * @return bool
     */
    public function isImportedFromBepado()
    {
        if ($this->getState() == 2) {
            return true;
        }
        return false;
    }

    /**
     * @return Product
     * @throws Exception
     */
    public function getSdkProduct()
    {
        /** @var mf_sdk_converter $converter */
        $converter = $this->getVersionLayer()->createNewObject('mf_sdk_converter');
        $sdkProduct = $converter->toBepadoProduct($this);

        if ($this->getState() == 0) {
            throw new Exception("Product is not imported from Bepado or ready for export to Bepado.");
        }

        $oState = $this->getVersionLayer()->createNewObject('oxbase');
        $oState->init('bepado_product_state');
        $oState->load($this->getId());

        $sdkProduct->shopId = $oState->bepado_product_state__shop_id->rawValue;

        return $sdkProduct;
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
}
 