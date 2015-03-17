<?php

/**
 * This extension add additional information to the details controller.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_details extends mf_details_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * The bepado own logging.
     *
     * @var mf_sdk_logger_helper
     */
    protected $logger;

    /**
     * @var mf_sdk_helper
     */
    protected $sdkHelper;

    /**
     * We just add marketplace information for imported products
     * and flag in the configuration.
     *
     * @return mixed
     */
    public function render()
    {
        $sTemplate = parent::render();

        /** @var mf_sdk_article_helper $articleHelper */
        $articleHelper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');
        $articleHelper->computeMarketplaceHintOnArticle($this->getProduct());

        return $sTemplate;
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
