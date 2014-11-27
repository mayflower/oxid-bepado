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

        $oxProduct = oxNew('oxarticle');
        $oxProduct->load($sProductID);

        // check if product is imported Bepado product
        if (!$oxProduct->isImportedFromBepado()) {
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

        $checked = $oxProduct->checkProductWithBepardo($oxProduct->getSdkProduct());

        // change Product according to check?

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
     * @param bool $blForceUpdate set this parameter to TRUE to force basket recalculation
     *
     * @return null
     */
    public function calculateBasket($blForceUpdate = false)
    {
        return parent::calculateBasket($blForceUpdate);
    }
} 