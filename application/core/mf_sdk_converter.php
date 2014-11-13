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

        /** @var \oxConfig $oShopConfig */
        $oShopConfig  = oxRegistry::get('oxConfig');
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency      = array_filter($currencyArray, function ($item) {
            return ($item['rate'] == '1.00');
        });

        $sdkProduct->sourceId         = $oxProduct->getId();
        $sdkProduct->title            = $oxProduct->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $oxProduct->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription  = $oxProduct->getLongDescription()->getRawValue();
        $sdkProduct->vendor           = $oxProduct->getVendor()->oxvendor__oxtitle->value;
        // Price is netto or brutto depending on ShopConfig
        if ('oxconfig__blEnterNetPrice' == 1) {
            $sdkProduct->price         = $oxProduct->oxarticles__oxprice->value * (1 + $oxProduct->getArticleVat());
            $sdkProduct->purchasePrice = $oxProduct->oxarticles__oxbprice->value;
        } else {
            $sdkProduct->price         = $oxProduct->oxarticles__oxprice->value;
            $sdkProduct->purchasePrice = $oxProduct->oxarticles__oxbprice->value / (1 + $oxProduct->getArticleVat());
        }
        $sdkProduct->currency         = $currency[0]['name'];
        $sdkProduct->availability     = $oxProduct->oxarticles__oxstock->value;
        $sdkProduct->vat              = $oxProduct->getArticleVat();

          /**               not fully implemented yet               */
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
        $aParams = [];

        $aParams['oxarticles__oxshopid'] = $sdkProduct->shopId;
        $aParams['oxarticles__oxtitle'] = $sdkProduct->title;
        $aParams['oxarticles__oxlongdesc'] = $sdkProduct->longDescription;
        $aParams['oxarticles__oxshortdesc'] = $sdkProduct->shortDescription;
        // Vendor: vendor name no use, only vendorId can load vendor object

        // Price is netto or brutto depending on ShopConfig
        // PurchasePrice is calculated accordingly by Shop
        if ('oxconfig__blEnterNetPrice' == 1) {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price;
        } else {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * (1 + $sdkProduct->vat);
        }
        $aParams['oxarticles__oxvat'] = $sdkProduct->vat;
        // Currency: unit won't initialize currency object
        $aParams['oxarticles__oxstock'] = $sdkProduct->availability;

        $oxProduct->assign($aParams);
        
        return $oxProduct;
    }

    /**
     * @param oxarticle $oxProduct
     *
     * @return array
     */
    private function mapImages($oxProduct)
    {
        // not done
        // return array has wrong structure ([int, string, bool, [], [], bool, []])

        return $oxProduct->getPictureGallery();
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
            // reference quantity is always 1 in oxid shop
            Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Product::ATTRIBUTE_QUANTITY           => $oxProduct->getUnitQuantity(),
        );
        return $attributes;
    }
} 