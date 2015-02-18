<?php

/**
 * As the base oxid eShop is not able to test that class, we won't be able to do that too.
 *
 * @codeCoverageIgnore
 *
 * This class extends the base oxOrder to get access into some methods.
 * We need to extend the finalize method to check for possible imported articles and create an
 * checkout in the the remote shop by the use of the SDK. We also need to get access to save() and
 * delete to recognize changes in an order state and send that information to bepado to inform the
 * remote shop.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_oxOrder extends mf_oxOrder_parent
{

    const BEPADO_RESPONSE_ORDER_PROBLEM_STATE = 9;

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
        /** @var mf_sdk_article_helper $articleHelper */
        $articleHelper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');

        $hasImports = $articleHelper->hasBasketImportedArticles($oBasket);

        $returnValue = parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        if ($hasImports) {
            /** @var mf_sdk_product_helper $productHelper */
            $productHelper = $this->getVersionLayer()->createNewObject('mf_sdk_product_helper');

            $reservation = null;
            try {
                $reservation = $productHelper->reserveProductsInOrder($this, $oUser);
                if (!$reservation) {
                    return $returnValue;
                }

                $productHelper->checkoutProducts($reservation, $this);
            } catch(Exception $e) {
                /** @var mf_sdk_logger_helper $logger */
                $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');
                $logger->writeBepadoLog('Problem while reserve or checking out the product: '.$e->getMessage());
                $this->getVersionLayer()->getDb()->rollbackTransaction();
                return self::BEPADO_RESPONSE_ORDER_PROBLEM_STATE;
            }
        }

        return $returnValue;
    }

    /**
     * {@inheritDoc}
     */
    public function save($updateOrderState = true)
    {
        $orderId = parent::save();
        $oOrder = oxNew("oxorder");
        $oOrder->load($orderId);
        if (!$oOrder->isLoaded() || !$updateOrderState) {
            return $orderId;
        }

        /** @var mf_sdk_order_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_order_helper');
        $helper->checkForOrderStateUpdates($oOrder);

        return $orderId;
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
        $helper->checkForOrderStateUpdates($oOrder, true);

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
