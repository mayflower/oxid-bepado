<?php

/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

use Bepado\SDK\ProductToShop;
use Bepado\SDK\Struct;

/**
 * One of the base implementation of SDK models.
 *
 * Is need for the communication of bepado to the shop.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
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
        /** @var mfProductConverter $productConverter */
        $productConverter = $this->getVersionLayer()->createNewObject('mfProductConverterChain');
        /** @var mf_sdk_logger_helper $logger */
        $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');

        $oxArticle = $this->getVersionLayer()->createNewObject('oxArticle');
        $productConverter->fromBepadoToShop($product, $oxArticle);
        /** @var mfBepadoProduct $oBepadoProduct */
        $oBepadoProduct = $this->getVersionLayer()->createNewObject('mfBepadoProduct');
        $select = $oBepadoProduct->buildSelectString(array(
            'p_source_id' => $product->sourceId,
            'shop_id' => $product->shopId
        ));

        if ($id = $this->getVersionLayer()->getDb(true)->getOne($select)) {
            $oBepadoProduct->load($id);
        }
        if (mfBepadoProduct::PRODUCT_STATE_EXPORTED === $oBepadoProduct->getState()) {
            $logger->writeBepadoLog(
                'Somebody tried to insert or update a bepado product, which is marked as an exported oxArticle.',
                array('product' => array('id' => $product->sourceId, 'name' => $product->title))
            );

            return;
        }

        if (mfBepadoProduct::PRODUCT_STATE_IMPORTED === $oBepadoProduct->getState()) {
            $this->updateArticle($oxArticle, $oBepadoProduct->getId());
        } else {
            $this->insertArticle($oxArticle, $oBepadoProduct, $product);
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
        /** @var mfBepadoProduct $oBepadoProduct */
        $oBepadoProduct = $this->getVersionLayer()->createNewObject('mfBepadoProduct');
        $select = $oBepadoProduct->buildSelectString(array(
            'p_source_id' => $sourceId,
            'shop_id' => $shopId
        ));
        $id = $this->getVersionLayer()->getDb(true)->getOne($select);
        if ($id) {
            $oBepadoProduct->load($id);
            $oBepadoProduct->delete();

            $oxArticle = $this->getVersionLayer()->createNewObject('oxArticle');
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
     * @param oxArticle       $oxArticle
     * @param mfBepadoProduct $oBepadoProduct
     * @param Struct\Product  $product
     */
    private function insertArticle(oxArticle $oxArticle, mfBepadoProduct $oBepadoProduct, Struct\Product $product)
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
        $oBepadoProduct->assign(array(
                'p_source_id' => $product->sourceId,
                'shop_id'     => $product->shopId,
                'state'       => mfBepadoConfiguration::ARTICLE_STATE_IMPORTED,
                'OXID'        => $oxArticle->getId(),
            )
        );
        $oBepadoProduct->save();
    }

    /**
     * When updating a product it should exist in the database as an oxArticle
     * and update its data.
     *
     * @param oxArticle $oxArticle
     */
    private function updateArticle(oxArticle $oxArticle, $persistedId)
    {

        $persistedoxArticle = $this->getVersionLayer()->createNewObject('oxArticle');
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
