<?php

/**
 * The modules extension of the oxid order view/controller.
 *
 * We need to step into the following points:
 *
 *  - render the order to have a chance to check bepado product states
 *  - finalizeOrder to reserver and checkout the optional bepado products
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_order extends mf_order_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    public function render()
    {
        $parent = parent::render();

        $oxBasket = $this->getBasket();

        /** @var mf_sdk_product_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_product_helper');
        $helper->checkProductsInBasket($oxBasket);

        return $parent;
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
