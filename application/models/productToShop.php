<?php

use Bepado\SDK\ProductToShop;
use Bepado\SDK\Struct;

class oxidProductToShop implements ProductToShop
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

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
     * Import or update given product
     *
     * Store product in your shop database as an external product. The
     * associated sourceId
     *
     * @param Struct\Product $product
     */
    public function insertOrUpdate(Struct\Product $product)
    {
        /** @var mf_sdk_converter $productConverter */
        $productConverter = $this->getVersionLayer()->createNewObject('mf_sdk_converter');

        $oxArticle = $productConverter->fromBepadoToShop($product);

        /** @var oxBase $oBepadoProductState */
        $oBepadoProductState = $this->getVersionLayer()->createNewObject('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $select = $oBepadoProductState->buildSelectString(array(
            'p_source_id' => $product->sourceId,
            'shop_id' => $product->shopId
        ));

        if ($id = $this->getVersionLayer()->getDb(true)->getOne($select)) {
            $oBepadoProductState->load($id);

        }
        if ($oBepadoProductState->isLoaded()) {
            $this->updateArticle($oxArticle, $oBepadoProductState->getId());
        } else {
            $this->insertArticleWithBepadoState($oxArticle, $oBepadoProductState, $product);
        }
    }

    /**
     * Delete product with given shopId and sourceId.
     *
     * Only the combination of both identifies a product uniquely. Do NOT
     * delete products just by their sourceId.
     *
     * You might receive delete requests for products, which are not available
     * in your shop. Just ignore them.
     *
     * @param string $shopId
     * @param string $sourceId
     * @return void
     */
    public function delete($shopId, $sourceId)
    {
        /** @var oxBase $oBepadoProductState */
        $oBepadoProductState = oxNew('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $select = $oBepadoProductState->buildSelectString(array(
            'p_source_id' => $sourceId,
            'shop_id' => $shopId
        ));
        $id = $this->getVersionLayer()->getDb(true)->getOne($select);
        if ($id) {
            $oBepadoProductState->load($id);
            $oBepadoProductState->delete();

            $oxArticle = oxNew('oxarticle');
            $oxArticle->load($id);
            $oxArticle->delete();
        }
    }

    /**
     * Start transaction
     *
     * Starts a transaction, which includes all insertOrUpdate and delete
     * operations, as well as the revision updates.
     *
     * @return void
     */
    public function startTransaction()
    {
        // TODO: Implement startTransaction() method.
    }

    /**
     * Commit transaction
     *
     * Commits the transactions, once all operations are queued.
     *
     * @return void
     */
    public function commit()
    {
        // TODO: Implement commit() method.
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }

    /**
     * Creating a new article means setting it inactive and create a
     * state entry.
     *
     * @param oxArticle      $oxArticle
     * @param oxBase         $oBepadoProductState
     * @param Struct\Product $product
     */
    private function insertArticleWithBepadoState(oxArticle $oxArticle, oxBase $oBepadoProductState, Struct\Product $product)
    {
        $oxArticle->assign(array(
            'oxarticles__oxactive' => 0,
            'oxarticles__oxstockflag' => 3,

        ));
        $oxArticle->save();

        // insert into mapping/state table
        $oBepadoProductState->assign(array(
                'p_source_id' => $product->sourceId,
                'shop_id'     => $product->shopId,
                'state'       => SDKConfig::ARTICLE_STATE_IMPORTED,
                'OXID'        => $oxArticle->getId(),
            )
        );
        $oBepadoProductState->save();
    }

    /**
     * When updating a product it should exist in the database as an oxArticle
     * and update its data.
     *
     * @param oxArticle $oxArticle
     */
    private function updateArticle(oxArticle $oxArticle, $persistedId)
    {

        $persistedoxArticle = $this->getVersionLayer()->createNewObject('oxarticle');
        $persistedoxArticle->load($persistedId);

        if (!$persistedoxArticle->isLoaded()) {
            return;
        }

        $fieldNames = $oxArticle->getFieldNames();
        $aParams = array();
        foreach ($fieldNames as $name) {
            if ('oxid' == $name) {
                continue;
            }
            $aParams['oxarticles__'.$name] = $oxArticle->getFieldData($name);
        }

        $persistedoxArticle->assign($aParams);
        $persistedoxArticle->save();
    }
}
