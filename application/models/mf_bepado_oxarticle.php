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
     * @var mf_sdk_article_helper
     */
    private $articleHelper;

    /**
     * @var SDK
     */
    private $sdk;

    /**
     * @return mf_sdk_article_helper
     */
    private function getArticleHelper()
    {
        if (null === $this->articleHelper) {
            $this->articleHelper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');
        }

        return $this->articleHelper;
    }

    /**
     * @return SDK
     */
    private function getSDK()
    {
        if (null === $this->sdk) {
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
            $config  = $helper->createSdkConfigFromOxid();
            $this->sdk = $helper->instantiateSdk($config);
        }

        return $this->sdk;
    }

    /**
     * Does the sdk work when saving an oxid article.
     */
    public function save()
    {
        $return = parent::save();

        $isExported = $this->getArticleHelper()->isArticleExported($this);
        $isKnown    = $this->productIsKnown($this->getId());

        if ($isExported && $isKnown) {
            $this->getSDK()->recordUpdate($this->getId());
        } elseif (!$isExported && $isKnown) {
            $this->getSDK()->recordDelete($this->getId());
        } elseif ($isExported && !$isKnown) {
            $this->getSDK()->recordInsert($this->getId());
        }

        return $return;
    }

    public function delete($oxId = null)
    {
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $config  = $helper->createSdkConfigFromOxid();
        /** @var SDK $sdk */
        $sdk = $helper->instantiateSdk($config);

        if ($this->getArticleHelper()->isArticleKnown($oxId)) {
            $sdk->recordDelete($oxId);
        }

        return parent::delete($oxId);
    }

    /**
     * @return Product
     * @throws Exception
     */
    public function getSdkProduct()
    {
        /** @var mf_sdk_converter $converter */
        $converter = $this->getVersionLayer()->createNewObject('mf_sdk_converter');
        $sdkProduct = $converter->fromShopToBepado($this);

        if ($this->getArticleHelper()->getArticleBepadoState($this) == 0) {
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
