<?php

use Bepado\SDK\Struct\Product;

class mf_sdk_converter //implements ProductConverter
{
    /**
     * @param oxarticle $oxProduct
     *
     * @return Product
     */
    public function toBepadoProduct(oxarticle $oxProduct)
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
        $sdkProduct->price            = $oxProduct->getPrice()->getNettoPrice();
        $sdkProduct->purchasePrice    = $oxProduct->getBasePrice();
        //                              $oxProduct->oxarticles__oxunitname->value ??
        $sdkProduct->currency         = $currency[0]['name'];
        $sdkProduct->availability     = $oxProduct->getStockStatus();
        $sdkProduct->vat              = $oxProduct->getArticleVat();

          /**                 not implemented yet                 */
        $sdkProduct->images           = $this->mapImages($oxProduct);
        $sdkProduct->categories       = $this->mapCategories($oxProduct);
        $sdkProduct->attributes       = $this->mapAttributes($oxProduct);

        return $sdkProduct;
    }

    /**
     * @param Product $sdkProduct
     *
     * @return oxarticle
     */
    public function toShopProduct(Product $sdkProduct)
    {
        /** @var oxarticle $oxProduct */
        $oxProduct = oxNew('oxarticle');

        $oxProduct->setId($sdkProduct->sourceId);
        $oxProduct->oxarticles__oxtitle = $sdkProduct->title;
        $oxProduct->oxarticles__oxshortdesc = $sdkProduct->shortDescription;
        // LongDescription
        $oxProduct->getVendor()->oxvendor__oxtitle = $sdkProduct->vendor;
        // Price
        // BasePrice
        // $oxProduct->oxarticles__oxunitname = $sdkProduct->currency;
        // StockStatus
        // Vat


        return $oxProduct;
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
        $dimension = sprintf('%fx%fx%f', [
            $oxProduct->oxarticles__oxlength->value,
            $oxProduct->oxarticles__oxwidth->value,
            $oxProduct->oxarticles__oxheight->value
        ]);

        $attributes = array(
            Product::ATTRIBUTE_WEIGHT             => $oxProduct->getWeight(),
            Product::ATTRIBUTE_VOLUME             => $oxProduct->getSize(),
            Product::ATTRIBUTE_DIMENSION          => $dimension,
            Product::ATTRIBUTE_UNIT               => $oxProduct->getUnitName(),
            // not done
            Product::ATTRIBUTE_REFERENCE_QUANTITY => '',
            Product::ATTRIBUTE_QUANTITY           => $oxProduct->getUnitQuantity(),
        );
        return $attributes;
    }
} 