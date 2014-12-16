<?php

/**
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_article_extend extends mf_article_extend_parent
{
    const SHOP_ID_LOCAL = '_self_';

    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * @var mf_sdk_article_helper
     */
    private $articleHelper;


    public function render()
    {
        $template = parent::render();

        $oxArticle = oxNew('oxarticle');
        $oxArticle->load($this->getEditObjectId());
        $state = $this->getArticleHelper()->getArticleBepadoState($oxArticle);

        if ($state != 2) {
            $this->_aViewData['export_to_bepado'] = $state;
            $this->_aViewData['no_bepado_import'] = 1;
        }

        return $template;
    }

    public function save()
    {
        $this->getArticleHelper()->onSaveArticleExtend($this->getEditObjectId());

        parent::save();
    }

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
