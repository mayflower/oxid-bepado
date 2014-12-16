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
     * When saving an article, the helper will do some work on
     * updating/deleting/inserting and entry to the SDK.
     */
    public function save()
    {
        $return = parent::save();

        $this->getArticleHelper()->onSaveArticleExtend($this);

        return $return;
    }

    /**
     * @param null $oxId
     *
     * @return mixed
     */
    public function delete($oxId = null)
    {
        $this->getArticleHelper()->onArticleDelete($this);

        return parent::delete($oxId);
    }

    /**
     * @deprecated Function is moved to the helper.
     *
     * @return Product
     * @throws Exception
     */
    public function getSdkProduct()
    {
        return $this->getArticleHelper()->computeSdkProduct($this);
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
