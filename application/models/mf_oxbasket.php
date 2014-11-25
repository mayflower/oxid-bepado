<?php


class mf_oxbasket extends mf_oxbasket_parent
{
    /**
     * @param string $sProductID       id of product
     * @param double $dAmount          product amount
     * @param mixed  $aSel             product select lists (default null)
     * @param mixed  $aPersParam       product persistent parameters (default null)
     * @param bool   $blOverride       marker to accumulate passed amount or renew (default false)
     * @param bool   $blBundle         marker if product is bundle or not (default false)
     * @param mixed  $sOldBasketItemId id if old basket item if to change it
     *
     * @throws oxOutOfStockException oxArticleInputException, oxNoArticleException
     *
     * @return object
     */
    public function addToBasket(
        $sProductID,
        $dAmount,
        $aSel = null,
        $aPersParam = null,
        $blOverride = false,
        $blBundle = false,
        $sOldBasketItemId = null
    ) {
        // init list of all elements in table bepado_product_state
        $oState = oxNew('oxlist');
        $oState->init('oxbase', 'bepado_product_state');
        $oState->getBaseObject();
        $oState->getList();
        $oState = $oState->getArray();

        // check if product is imported Bepado product
        if (!array_key_exists($sProductID, $oState)) {
            return parent::addToBasket(
                $sProductID,
                $dAmount,
                $aSel,
                $aPersParam,
                $blOverride,
                $blBundle,
                $sOldBasketItemId
            );
        }
        
        $sdkProduct = $this->_getSdkProduct($sProductID);
        $checked = $this->_checkProductWithBepado($sdkProduct);


        return parent::addToBasket(
            $sProductID,
            $dAmount,
            $aSel,
            $aPersParam,
            $blOverride,
            $blBundle,
            $sOldBasketItemId
        );
    }

    /**
     * @param string $sProductID
     *
     * @return \Bepado\SDK\Struct\Product
     */
    private function _getSdkProduct($sProductID)
    {
        $oxProduct = oxNew('oxarticle');
        $oxProduct->load($sProductID);

        $sdkProduct = $oxProduct->getSdkProduct();

        $oState = oxNew('oxbase');
        $oState->init('bepado_product_state');
        $oState->load($sProductID);

        $sdkProduct->shopId = $oState->bepado_product_state__shop_id->rawValue;

        return $sdkProduct;
    }

    /**
     * @param $sdkProduct
     *
     * @return null|bool|array
     */
    private function _checkProductWithBepado($sdkProduct)
    {
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = oxNew('mf_sdk_helper');
        $sdkConfig = $sdkHelper->createSdkConfigFromOxid();
        $sdk = $sdkHelper->instantiateSdk($sdkConfig);

        $result = null;

        try {
            $results = $sdk->checkProducts(array($sdkProduct));
            $result = $results[0];

        } catch (\Exception $e) {
            var_dump($e);
        }

        return $result;
    }

    /**
     * @param bool $blForceUpdate set this parameter to TRUE to force basket recalculation
     *
     * @return null
     */
    public function calculateBasket($blForceUpdate = false)
    {
        return parent::calculateBasket($blForceUpdate);
    }
} 