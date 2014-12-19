<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_oxOrder extends mf_oxOrder_parent
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

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
        /** @var mf_sdk_logger_helper $logger */
        $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');
        /** @var mf_sdk_product_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_product_helper');

        $reservation = null;
        try {
            $reservation = $helper->reserveProductsInOrder($this);
            if (!$reservation) {
                return $returnValue;
            }

            $helper->checkoutProducts($reservation, $this);
        } catch(Exception $e) {
            $logger->writeBepadoLog('Problem while checking out the product: '.$e->getMessage());
            $this->getVersionLayer()->getDb()->rollbackTransaction();
            return oxOrder::ORDER_STATE_INVALIDPAYMENT;
        }

        return $returnValue;
    }

    /**
     * {@inheritDoc}
     */
    public function save()
    {
        $sOxId = parent::save();

        $oOrder = oxNew("oxorder");
        if ($sOxId != "-1") {
            $oOrder->load($sOxId);
        } else {
            return;
        }

        /** @var mf_sdk_order_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_order_helper');
        $helper->updateOrderStatus($oOrder);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($sOxId = null)
    {
        if ($sOxId) {
            if (!$this->load($sOxId)) {
                // such order does not exist
                return false;
            }
        } elseif (!$sOxId) {
            $sOxId = $this->getId();
        }

        // no order id is passed
        if (!$sOxId) {
            return false;
        }
        $oOrder = oxNew("oxorder");
        $oOrder->load($sOxId);

        /** @var mf_sdk_order_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_order_helper');
        $helper->updateOrderStatus($oOrder, 1);

        parent::delete($sOxId);
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
}
