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

use Bepado\SDK\SDK;
use Bepado\SDK\Struct\Product;

/**
 * This helper will encapsulate some common methods/functions on oxArticles and
 * oxOrderArticles.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_article_helper extends mf_abstract_helper
{
    /**
     * @var SDK
     */
    protected $sdk;

    /**
     * Articles that are imported from a remote shop got a true response.
     *
     * @param oxArticle $article
     *
     * @return bool
     */
    public function isArticleImported(oxArticle $article)
    {
        return $this->getBepadoState($article->getId()) === mfBepadoConfiguration::ARTICLE_STATE_IMPORTED;
    }

    /**
     * Articles in orders, that where imported from a remote shop got a true response.
     *
     * @param oxOrderArticle $orderArticle
     *
     * @return bool
     */
    public function isOrderArticleImported(oxOrderArticle $orderArticle)
    {
        return $this->getBepadoState($orderArticle->getArticle()->getId()) === mfBepadoConfiguration::ARTICLE_STATE_IMPORTED;
    }

    /**
     * Articles, that are marked as "exported for bepado" will get a true response.
     *
     * @param oxArticle $article
     *
     * @return bool
     */
    public function isArticleExported(oxArticle $article)
    {
        return $this->getBepadoState($article->getId()) === mfBepadoConfiguration::ARTICLE_STATE_EXPORTED;
    }

    /**
     * Depending on the state this method will respond states as int values.
     *
     * @param oxArticle $article
     *
     * @return int
     */
    public function getArticleBepadoState(oxArticle $article)
    {
        return $this->getBepadoState($article->getId());
    }

    /**
     * Will compute the state value for a given article id.
     *
     * @param $articleId
     *
     * @return int
     */
    public function getBepadoState($articleId)
    {
        /** @var mfBepadoProduct $oBepadoProduct */
        $oBepadoProduct = $this->getVersionLayer()->createNewObject('mfBepadoProduct');
        $oBepadoProduct->load($articleId);

        return $oBepadoProduct->getState();
    }

    /**
     * Action that happens, when an article is saved on article extend controller/view.
     *
     * @param $articleId
     */
    public function onSaveArticleExtend($articleId)
    {
        $oBepadoProduct = $this->createBepadoProductState($articleId);
        $aParams = $this->getVersionLayer()->getConfig()->getRequestParameter("editval");
        $articleState = isset($aParams['export_to_bepado']) &&  "1" === $aParams['export_to_bepado'] ? true : false;

        if ($oBepadoProduct->isLoaded() && !$articleState) {
            $oBepadoProduct->delete();
        } elseif (!$oBepadoProduct->isLoaded() && $articleState) {
            $oBepadoProduct->assign(array(
                    'p_source_id' => $articleId,
                    'OXID'        => $articleId,
                    'shop_id'     => '_self_',
                    'state'       => mfBepadoConfiguration::ARTICLE_STATE_EXPORTED,
                )
            );
            $oBepadoProduct->save();
        }
    }

    /**
     * @param $articleId
     */
    public function rollbackArticleExport($articleId)
    {
        $oBepadoProductState = $this->createBepadoProductState($articleId);
        $oBepadoProductState->delete();
    }

    /**
     *
     * @param $oxArticleId
     *
     * @return mfBepadoProduct
     */
    private function createBepadoProductState($oxArticleId)
    {
        /** @var mfBepadoProduct $oBepadoProduct */
        $oBepadoProduct = $this->getVersionLayer()->createNewObject('mfBepadoProduct');
        $oBepadoProduct->load($oxArticleId);

        return $oBepadoProduct;
    }

    /**
     * When an article is saved, we need to wheter an article is known and needs to be
     * updated to the SDK or isn't known and needs to be inserted. When removing the
     * exported flag from the article we need to delete its information from the SDK.
     *
     * @param oxArticle $oxArticle
     */
    public function onArticleSave(oxArticle $oxArticle)
    {
        $isExported = $this->isArticleExported($oxArticle);
        $isKnown    = $this->productIsKnown($oxArticle->getId());

        if ($isExported && $isKnown) {
            $this->getSDK()->recordUpdate($oxArticle->getId());
        } elseif (!$isExported && $isKnown) {
            $this->getSDK()->recordDelete($oxArticle->getId());
        } elseif ($isExported && !$isKnown) {
            $this->getSDK()->recordInsert($oxArticle->getId());
        }
    }

    /**
     * A bepado product (remote product) is persisted by its id as p_source_id in the bepado product state table.
     *
     * @param $oxProductId
     *
     * @return bool
     */
    private function productIsKnown($oxProductId)
    {
        $sql = "SELECT * FROM bepado_product WHERE `p_source_id` LIKE '" . $oxProductId."'";
        $result = $this->getVersionLayer()->getDb(true)->execute($sql);

        return count($result->getArray()) > 0;
    }

    /**
     * An own means oxid article is persisted with its own id as OXID in the bepado product state table.
     *
     * @param $articleId
     *
     * @return bool
     */
    private function isArticleKnown($articleId)
    {
        $sql = "SELECT * FROM mfbepadoproducts WHERE `OXID` LIKE '" . $articleId."'";
        $result = $this->getVersionLayer()->getDb(true)->execute($sql);

        return count($result->getArray()) > 0;
    }

    /**
     * When a article is deleted, that is imported from bepado, we need
     * to delete the entry from the bepado state table.
     *
     * @param oxArticle $oxArticle
     */
    public function onArticleDelete(oxArticle $oxArticle)
    {
        if ($this->isArticleKnown($oxArticle->getId())) {
            $this->getSDK()->recordDelete($oxArticle->getId());
        }
    }

    /**
     * Computes a SDK Product out of a managed OXID article.
     *
     * @param oxArticle $oxArticle
     *
     * @return Product
     *
     * @throws Exception
     */
    public function computeSdkProduct(oxArticle $oxArticle)
    {
        $oBepadoProduct = $this->createBepadoProductState($oxArticle->getId());
        $state = $oBepadoProduct->getState();

        if ($state == mfBepadoProduct::PRODUCT_STATE_NONE) {
            throw new Exception("Article is not managed for bepado. Neither exported to a remote shop nor imported.");
        }

        /** @var mfProductConverter $converter */
        $converter = $this->getVersionLayer()->createNewObject('mfProductConverterChain');
        $sdkProduct = new Product();
        $converter->fromShopToBepado($oxArticle, $sdkProduct);
        $sdkProduct->shopId = $oBepadoProduct->getShopId();
        $sdkProduct->sourceId = $oBepadoProduct->getProductSourceId();

        return $sdkProduct;
    }

    /**
     * @param oxBasket $basket
     * @return bool
     */
    public function hasBasketImportedArticles(oxBasket $basket)
    {
        /** @var  oxBasketItem[] $aBasket */
        $aBasket = $basket->getContents();

        foreach ($aBasket as $basketItem) {
            /** @var mf_bepado_oxarticle $basketArticle */
            $basketArticle = $basketItem->getArticle();

            if ($this->isArticleImported($basketArticle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Just a little helper to create an SDK instance
     *
     * @return SDK
     */
    private function getSDK()
    {
        if (null === $this->sdk) {
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
            $this->sdk = $helper->instantiateSdk();
        }

        return $this->sdk;
    }

    /**
     * With the given configuration an for imported articles this
     * method will add information to display the marketplace shop.
     *
     * @param oxArticle $oxArticle
     *
     * @throws Exception
     */
    public function computeMarketplaceHintOnArticle(oxArticle $oxArticle)
    {
        $oxArticle->marketplace_shop = null;
        if (!$this->isArticleImported($oxArticle)) {
            return;
        }

        /** @var mfBepadoConfiguration $oBepadoConfiguration Needed for the marketplace hint in the basket. */
        $oBepadoConfiguration = $this->getVersionLayer()->createNewObject('mfBepadoConfiguration');
        $shopId = $this->getVersionLayer()->getConfig()->getShopId();
        $oBepadoConfiguration->load($shopId);
        if (!$oBepadoConfiguration->isLoaded()) {
            $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper')->writeBepadoLog('No bepado configuration found for shopId '.$shopId);
            return;
        }

        if ($oBepadoConfiguration->hastShopHintOnArticleDetails()) {
            $oxArticle->marketplace_shop = $this->getVersionLayer()
                ->createNewObject('mf_sdk_helper')
                ->computeMarketplaceHintForProduct($oBepadoConfiguration, $this->computeSdkProduct($oxArticle))
            ;
        }
    }

    /**
     * An admin can decide which of the free fields (A,B or C) to use for the purchase price
     * mapping. This method will create a string which represents an oxArticle field name like
     * oxarticles__oxpriceb (a/c).
     *
     * @return string
     */
    public function getPurchasePriceField()
    {
        $sShopId = $this->getVersionLayer()->getConfig()->getShopId();
        /** @var mfBepadoConfiguration $oBepadoConfiguration */
        $oBepadoConfiguration = $this->getVersionLayer()->createNewObject('mfBepadoConfiguration');
        $oBepadoConfiguration->load($sShopId);
        $sChar = $oBepadoConfiguration->getPurchaseGroup();

        return 'oxarticles__oxprice'.strtolower($sChar);
    }
}
