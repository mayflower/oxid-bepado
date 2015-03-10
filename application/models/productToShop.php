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

        $this->computeCategoryChanges($oxArticle, $product);
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
        $oBepadoProductState = $this->getVersionLayer()->createNewObject('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $select = $oBepadoProductState->buildSelectString(array(
            'p_source_id' => $sourceId,
            'shop_id' => $shopId
        ));
        $id = $this->getVersionLayer()->getDb(true)->getOne($select);
        if ($id) {
            $oBepadoProductState->load($id);
            $oBepadoProductState->delete();

            $oxArticle = $this->getVersionLayer()->createNewObject('oxarticle');
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
        $this->getVersionLayer()->getDb()->startTransaction();
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
        $this->getVersionLayer()->getDb()->commitTransaction();
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
        /** @var mf_article_number_generator $articleNumberGenerator */
        $articleNumberGenerator = $this->getVersionLayer()->createNewObject('mf_article_number_generator');
        $oxArticle->assign(array(
            'oxarticles__oxactive'    => 0,
            'oxarticles__oxstockflag' => 3,
            'oxarticles__oxartnum'    => $articleNumberGenerator->generate(),

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

    /**
     * @param $oxArticle oxArticle
     * @param $product   Struct\Product
     */
    private function computeCategoryChanges(oxArticle $oxArticle, Struct\Product $product)
    {
        // clear possible former mapping entries
        $query = "DELETE FROM oxobject2category WHERE OXOBJECTID LIKE '".$oxArticle->getId()."'";
        $this->getVersionLayer()->getDb(true)->query($query);

        if (count($product->categories) === 0) {
            return;
        }

        /** @var oxList $bepadoCategoryMapping */
        $bepadoCategoryMapping = $this->getVersionLayer()->createNewObject('oxlist');
        $bepadoCategoryMapping->init('oxbase', 'bepado_categories');
        $bepadoCategoryMapping->getBaseObject();
        $bepadoCategoryMapping->getList();
        $bepadoCategories = $bepadoCategoryMapping->getArray();

        foreach ($product->categories as $categoryPath) {
            $match = array_filter($bepadoCategories, function ($category) use ($categoryPath) {
                return $categoryPath === $category->getFieldData('bepado_categories__path');
            });

            if (!$match || !is_array($match)) {
                continue; // bepado category not mapped a an oxid category
            }

            $oxidCategory = array_shift($match);
            $object2category = $this->getVersionLayer()->createNewObject('oxObject2Category');
            $values = array(
                'oxobject2category__oxobjectid' => $oxArticle->getId(),
                'oxobject2category__oxcatnid'   => $oxidCategory->getId(),
            );
            $object2category->assign($values);

            $object2category->save();
        }
    }
}
