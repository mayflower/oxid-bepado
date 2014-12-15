<?php

/**
 * This helper will encapsulate some common methods/functions on oxArticles and
 * oxOrderArticles.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_article_helper extends mf_abstract_helper
{
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
}
