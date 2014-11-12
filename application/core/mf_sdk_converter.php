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
        $sdkProduct->price            = $oxProduct->getPrice()->getNettoPrice();
        $sdkProduct->purchasePrice    = $oxProduct->oxarticles__oxbprice->value;;
        $sdkProduct->currency         = $currency[0]['name'];
        $sdkProduct->availability     = $oxProduct->oxarticles__oxstock->value;
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

        // ID: cannot be source ID, which has no representation in oxarticle object
        // Title; oxarticle has no title or name property
        // ShortDesc: oxarticle has no shortDesc property
        $oxProduct->setArticleLongDesc($sdkProduct->longDescription);
        // Vendor: vendor name no use, only vendorId can load vendor object
        // Price: first set netto mode, then set price
        $oxProduct->getPrice()->setNettoPriceMode();
        $oxProduct->getPrice()->setPrice($sdkProduct->price);
        $oxProduct->getPrice()->setVat($sdkProduct->vat);
        // BasePrice: no setter..?
        // Currency: unit won't initialize currency object
        // StockStatus: var $_iStockStatus has no setter and is protected


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