<?php

use Bepado\SDK\Struct\Product;

class mf_sdk_converter 
{
    /**
     * @param oxarticle $oxProduct
     *
     * @return Product
     */
    public function oxArticleToBepardo(oxarticle $oxProduct)
    {
        $sdkProduct = new Product();

        /** @var \oxConfig $oxShopConfig */
        $oxShopConfig  = oxRegistry::get('oxConfig');
        $currencyArray = $oxShopConfig->getCurrencyArray();

        $currency      = array_filter($currencyArray, function ($item) {
            return ($item['rate'] == '1.00');
        });

        $sdkProduct->sourceId         = $oxProduct->getId();
        $sdkProduct->title            = $oxProduct->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $oxProduct->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription  = $oxProduct->getLongDescription();
        $sdkProduct->vendor           = $oxProduct->getVendor()->oxvendor__oxtitle->value;
        $sdkProduct->price            = $oxProduct->getPrice();
        $sdkProduct->purchasePrice    = $oxProduct->getBasePrice();
        $sdkProduct->currency         = $currency[0]['name'];
        $sdkProduct->availability     = $oxProduct->getStockStatus();
        $sdkProduct->vat              = $oxProduct->getArticleVat();

        $sdkProduct->images           = $this->mapImages($oxProduct);
        $sdkProduct->categories       = $this->mapCategories($oxProduct);
        $sdkProduct->attributes       = $this->mapAttributes($oxProduct);

        return $sdkProduct;
    }

    /**
     * @param oxarticle $oxProduct
     *
     * @return array
     */
    private function mapImages($oxProduct)
    {
        return array(); // links zu bildern. 1. bild ist hauptbild
    }

    /**
     * @param oxarticle $oxProduct
     *
     * @return array
     */
    private function mapCategories($oxProduct)
    {
        // Oxid Kategorien auf bepado/Google Shopping mappen
        // Zwei Möglichkeiten:
        // - im einfachsten Fall an in einer Extra Tabelle zu einem Produkt die Google Kategorie zu konfigurieren.
        // - Endbenutzer generisches Mapping seiner Kategorien auf Google Kategorien erlauben
        //
        // Die Kategorien können in der UI über $sdk->getCategories(); abgefragt werden.
        //
        return array();
    }

    /**
     * @param oxarticle $oxProduct
     *
     * @return array
     */
    private function mapAttributes($oxProduct)
    {
        // Am Anfang nicht zwingend notwendig, aber hier werden die ATTRIBUTE_ Konstanten
        // auf Bepado\SDK\Struct\Product als Keys gesetzt, die für
        // Grundpreisbrechnung notwendig sind.

        $attributes = array(
            'weight'       => $oxProduct->getWeight(),
            'volume'       => '',
            'dimension'    => $oxProduct->getSize(),
            'unit'         => $oxProduct->getUnitName(),
            'ref_quantity' => '',
            'quantity'     => $oxProduct->getUnitQuantity(),
        );
        return $attributes;
    }
} 