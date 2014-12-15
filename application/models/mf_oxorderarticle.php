<?php


class mf_oxorderarticle extends mf_oxorderarticle_parent
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
     * Copies passed to method product into $this.
     *
     * @param oxarticle $oProduct product to copy
     */
    public function copyThis($oProduct)
    {
        $state = $this->getArticleHelper()->getArticleBepadoState($oProduct);

        if ($state == 2) {
            $oProduct->oxarticles__imported = new oxField(
                1,
                oxField::T_RAW
            );
        }

        parent::copyThis($oProduct);
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