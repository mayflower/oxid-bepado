<?php

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
        return $this->getBepadoState($article->getId()) === SDKConfig::ARTICLE_STATE_IMPORTED;
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
        return $this->getBepadoState($orderArticle->getArticle()->getId()) === SDKConfig::ARTICLE_STATE_IMPORTED;
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
        return $this->getBepadoState($article->getId()) === SDKConfig::ARTICLE_STATE_EXPORTED;
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
     * @param $articleId
     *
     * @return int
     */
    private function getBepadoState($articleId)
    {
        /** @var oxBase $oBepadoProductState */
        $oBepadoProductState = $this->getVersionLayer()->createNewObject('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $oBepadoProductState->load($articleId);

        if (!$oBepadoProductState->isLoaded()) {
            return SDKConfig::ARTICLE_STATE_NONE;
        }
        $state = (int) $oBepadoProductState->getFieldData('state');

        return !$state ? SDKConfig::ARTICLE_STATE_NONE : $state;
    }

    /**
     * Action that happens, when an article is saved on article extend controller/view.
     *
     * @param $articleId
     */
    public function onSaveArticleExtend($articleId)
    {
        $oBepadoProductState = $this->createBepadoProductState($articleId);
        $aParams = $this->getVersionLayer()->getConfig()->getRequestParameter("editval");
        $articleState = isset($aParams['export_to_bepado']) &&  "1" === $aParams['export_to_bepado'] ? true : false;

        if ($oBepadoProductState->isLoaded() && !$articleState) {
            $oBepadoProductState->delete();
        } elseif (!$oBepadoProductState->isLoaded() && $articleState) {
            $oBepadoProductState->assign(array(
                    'p_source_id' => $articleId,
                    'OXID'        => $articleId,
                    'shop_id'     => '_self_',
                    'state'       => SDKConfig::ARTICLE_STATE_EXPORTED,
                )
            );
            $oBepadoProductState->save();
        }
    }

    /**
     *
     * @param $oxArticleId
     *
     * @return oxBase
     */
    private function createBepadoProductState($oxArticleId)
    {
        /** @var oxBase $oBepadoProductState */
        $oBepadoProductState = $this->getVersionLayer()->createNewObject('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $select = $oBepadoProductState->buildSelectString(array('p_source_id' => $oxArticleId, 'shop_id' => SDKConfig::SHOP_ID_LOCAL));
        $id = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oBepadoProductState->load($id);

        return $oBepadoProductState;
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
        $sql = "SELECT * FROM bepado_product WHERE `OXID` LIKE '" . $articleId."'";
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
     * Computes a SDK Product out of an oxid article.
     *
     * @param oxArticle $oxArticle
     *
     * @return Product
     *
     * @throws Exception
     */
    public function computeSdkProduct(oxArticle $oxArticle)
    {
        $oState = $this->getVersionLayer()->createNewObject('oxbase');
        $oState->init('bepado_product_state');
        $oState->load($oxArticle->getId());
        $state = (int) $oState->getFieldData('state');

        if ($state !== SDKConfig::ARTICLE_STATE_EXPORTED && $state !== SDKConfig::ARTICLE_STATE_IMPORTED) {
            throw new Exception("Article is not managed for bepado. Neither exported to a remote shop nor imported.");
        }

        /** @var mf_sdk_converter $converter */
        $converter = $this->getVersionLayer()->createNewObject('mf_sdk_converter');
        $sdkProduct = $converter->fromShopToBepado($oxArticle);

        $sdkProduct->shopId = $oState->getFieldData('shop_id');
        $sdkProduct->sourceId = $oState->getFieldData('p_source_id');

        return $sdkProduct;
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
            $config  = $helper->createSdkConfigFromOxid();
            $this->sdk = $helper->instantiateSdk($config);
        }

        return $this->sdk;
    }
}
