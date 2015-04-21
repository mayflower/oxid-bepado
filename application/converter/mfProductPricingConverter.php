<?php

use Bepado\SDK\Struct\Product;

/**
 * The pricing converter is responsible to convert prices and currencies.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductPricingConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * Default value for the purchase price chat.
     * This char decides which price should be chose for the purchase prise.
     */
    const DEFAULT_PURCHASE_PRICE_CHAR = 'B';

    /**
     * @var mf_module_helper
     */
    protected $moduleHelper;

    /**
     * {@inheritDoc}
     *
     * @param object $shopObject
     * @param object $bepadoObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {
        $oShopConfig = $this->getVersionLayer()->getConfig();

        // create the currency
        $currencyArray = $oShopConfig->getCurrencyArray();
        $currency     = array_filter($currencyArray, function ($item) {
            return $item->rate === '1.00';
        });
        $currency = array_shift($currency);
        $bepadoObject->currency = $currency->name;

        // create pricing and vat
        $bepadoObject->vat = $shopObject->getArticleVat() / 100;
        $bepadoObject->price = $this->getModuleHelper()->createNetPrice($shopObject->getPrice());

        // create the purchase price with the matching mode
        $purchasePrice = new oxPrice();
        $purchasePrice->setVat($shopObject->getArticleVat());

        $purchasePrice->setPrice($shopObject->{$this->computePurchasePriceField($shopObject)}->value);
        $bepadoObject->purchasePrice = $this->getModuleHelper()->createNetPrice($purchasePrice);

        if (!$bepadoObject->purchasePrice) {
            $bepadoObject->purchasePrice = $bepadoObject->price;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param Product $bepadoObject
     * @param oxArticle $shopObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {
        $aParams = [];
        $rate = $this->computeCurrencyRate($bepadoObject);

        // Price is netto or brutto depending on ShopConfig
        if (oxRegistry::getConfig()->getConfigParam('blEnterNetPrice')) {
            $aParams['oxarticles__oxprice'] = $bepadoObject->price * $rate;
            $aParams[$this->computePurchasePriceField()] = $bepadoObject->purchasePrice * $rate;
        } else {
            $aParams['oxarticles__oxprice'] = $bepadoObject->price * (1 + $bepadoObject->vat) * $rate;
            $aParams[$this->computePurchasePriceField()] = $bepadoObject->purchasePrice * (1 + $bepadoObject->vat) * $rate;
        }
        $aParams['oxarticles__oxvat'] = $bepadoObject->vat * 100;

        $shopObject->assign($aParams);
    }

    /**
     * Depending on the module config
     *
     * @return string
     */
    private function computePurchasePriceField()
    {
        /** @var mfBepadoConfiguration $bepadoConfiguration */
        $bepadoConfiguration = $this->getVersionLayer()->createNewObject('mfBepadoConfiguration');
        $shopId = $this->getVersionLayer()->getConfig()->getShopId();
        $bepadoConfiguration->load($shopId);
        $purchaseGroupChar = $bepadoConfiguration->getPurchaseGroup();
        if (!in_array($purchaseGroupChar, array('A', 'B', 'C'))) {
            $purchaseGroupChar = self::DEFAULT_PURCHASE_PRICE_CHAR;
        }
        $purchaseGroupChar = strtolower($purchaseGroupChar);

        return 'oxarticles__oxprice'.$purchaseGroupChar;
    }

    /**
     * Getter for the module helper.
     *
     * @return mf_module_helper
     */
    private function getModuleHelper()
    {
        if (null == $this->moduleHelper) {
            $this->moduleHelper = $this->getVersionLayer()->createNewObject('mf_module_helper');
        }

        return $this->moduleHelper;
    }

    /**
     * Computes the rate to work with for the current currency.
     *
     * @param $bepadoObject
     *
     * @return int
     */
    protected function computeCurrencyRate($bepadoObject)
    {
        $currencyArray = $this->getVersionLayer()->getConfig()->getCurrencyArray();
        $currency = array_filter($currencyArray, function ($item) use ($bepadoObject) {
            return $item->name === $bepadoObject->currency;
        });
        $currency = array_shift($currency);
        $rate = $currency->rate;

        return (int) $rate;
    }
}
