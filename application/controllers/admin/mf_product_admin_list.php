<?php

/**
 * Just some abstraction for both mf_product_..._list classes as we have some common logic in there.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_admin_list extends oxAdminList
{
    /**
     * @var VersionLayerInterface
     */
    protected $_oVersionLayer;

    /**
     * A list of shops with its keys.
     *
     * @var array
     */
    protected $shops = array();

    /**
     * Each of the shops got its configuration in the bepado system.
     * So this method asks the sdk for getting them.
     * To avoid multiple requests, we do cache them.
     *
     * @param $shopId
     *
     * @return Shop
     */
    public function getShopById($shopId)
    {
        if (isset($this->shops[$shopId])) {
            return $this->shops[$shopId];
        }
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $sdk = $sdkHelper->instantiateSdk();
        $shop = $sdk->getShop($shopId);
        // for unknown shops
        if (null === $shop->name) {
            $shop->name = 'Unknown';
        }
        $this->shops[$shopId] = $shop;

        return $shop;
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    protected function getVersionLayer()
    {
        if (null == $this->_oVersionLayer) {
            /** @var VersionLayerFactory $factory */
            $factory = oxNew('VersionLayerFactory');
            $this->_oVersionLayer = $factory->create();
        }

        return $this->_oVersionLayer;
    }

    /**
     * Depending on the state this method filters a list of oxArticles.
     *
     * In the list of exported articles these should be shown only. Same for
     * the list of imported articles. They are filtered by their entries in
     * our mfbepadoproducts table.
     *
     * @param array|mfBepadoProduct $list
     * @param int   $state
     *
     * @return array
     */
    protected function filterArticlesByState($list, $state)
    {
        foreach ($list as $key => $item)  {
            if ($item->getState() !== $state) {
                unset($list[$key]);
                continue;
            }

            /** @var oxArticle $oArticle */
            $oArticle = $this->getVersionLayer()->createNewObject('oxArticle');
            $oArticle->load($item->mfbepadoproducts__oxid->value);
            $list[$key]->setOxArticle($oArticle);
        }

        return $list;
    }
}
