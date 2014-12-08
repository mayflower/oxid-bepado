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

        $oxBasket = $this->_aViewData['oxcmp_basket'];

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

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }

    /**
     * After finalizing the order from the oxid point of view, we will start to
     * have a look for bepado products and reserve/checkout the remote products.
     * The methods on the helper will throw the exceptions the order::execute will fetch
     * and hopefully display problems.
     *
     * @param oxBasket $oBasket
     * @param $oUser
     * @param bool $blRecalculatingOrder
     *
     * @return mixed
     */
    public function finalizeOrder(oxBasket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        $returnValue = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        /** @var mf_sdk_product_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_product_helper');
        $reservation = $helper->reserveProductsInOrder($this);
        if (!$reservation) {
            return $returnValue;
        }
        $helper->checkoutProducts($reservation, $this);

        return $returnValue;
    }
}
