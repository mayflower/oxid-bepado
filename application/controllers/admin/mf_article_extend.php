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

    public function render()
    {
        $template = parent::render();

        $oBepadoProductState = $this->createBepadoProductState();
        $this->_aViewData['export_to_bepado'] = $oBepadoProductState->isLoaded() ? 1 : 0;

        return $template;
    }

    public function save()
    {
        parent::save();

        $oBepadoProductState = $this->createBepadoProductState();
        $aParams = oxRegistry::getConfig()->getRequestParameter("editval");
        $articleState = isset($aParams['export_to_bepado']) &&  "1" === $aParams['export_to_bepado'] ? true : false;
        if ($oBepadoProductState->isLoaded() && !$articleState) {
            $oBepadoProductState->delete();
        } elseif (!$oBepadoProductState->isLoaded() && $articleState) {
            $oBepadoProductState->assign(array(
                    'p_source_id' => $this->getEditObjectId(),
                    'shop_id'     => '_self_',
                    'state'       => SDKConfig::ARTICLE_STATE_EXPORTED,
                )
            );
            $oBepadoProductState->save();
        }

    }

    /**
     * @return oxBase
     */
    private function createBepadoProductState()
    {
        $oxArticleId = $this->getEditObjectId();
        /** @var oxBase $oBepadoProductState */
        $oBepadoProductState = oxNew('oxbase');
        $oBepadoProductState->init('bepado_product_state');
        $select = $oBepadoProductState->buildSelectString(array('p_source_id' => $oxArticleId, 'shop_id' => self::SHOP_ID_LOCAL));
        $id = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oBepadoProductState->load($id);

        return $oBepadoProductState;
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
 