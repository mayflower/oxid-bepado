<?php

use Bepado\SDK\Struct\Product;

class mf_sdk_converter //implements ProductConverter
{
    const DEFAULT_UNIT = 'kg';

    private $oxidUnitMapper = array(
        '-' => self::DEFAULT_UNIT,
        '_UNIT_KG' => 'kg',
        '_UNIT_G' => 'g',
        '_UNIT_L' => 'l',
        '_UNIT_ML' => 'ml',
        '_UNIT_CM' => 'cm',
        '_UNIT_MM' => 'mm',
        '_UNIT_M' => 'm',
        '_UNIT_M2' => 'm^2',
        '_UNIT_M3' => 'm^3',
        '_UNIT_PIECE' => 'piece',
        '_UNIT_ITEM' => self::DEFAULT_UNIT,
    );

    /**
     * @param oxarticle $oxProduct
     *
     * @return Product
     */
    public function toBepadoProduct(oxarticle $oxProduct)
    {
        $sdkProduct = new Product();

        /** @var \oxConfig $oShopConfig */
        $oShopConfig = oxRegistry::get('oxConfig');
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency     = array_filter($currencyArray, function ($item) {
            return $item->rate === '1.00';
        });
        $currency = array_shift($currency);

        $sdkProduct->sourceId = $oxProduct->getId();
        $sdkProduct->title = $oxProduct->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $oxProduct->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription = $oxProduct->getLongDescription()->getRawValue();
        $sdkProduct->vendor = $oxProduct->getVendor()->oxvendor__oxtitle->value;
        $sdkProduct->vat = $oxProduct->getArticleVat() / 100;
        // Price is netto or brutto depending on ShopConfig
        $priceValue = (float) $oxProduct->oxarticles__oxprice->value;
        if ('oxconfig__blEnterNetPrice' == 1) {
            $sdkProduct->price = $priceValue  * (1 + $sdkProduct->vat);
            $sdkProduct->purchasePrice = $priceValue;
        } else {
            $sdkProduct->price = $priceValue;
            $sdkProduct->purchasePrice = $priceValue / (1 + $sdkProduct->vat);
        }
        $sdkProduct->currency = $currency->name;
        $sdkProduct->availability = $oxProduct->oxarticles__oxstock->value;

        /**               not fully implemented yet               */
        $sdkProduct->images = $this->mapImages($oxProduct);
        $sdkProduct->categories = $this->mapCategories($oxProduct);
        $sdkProduct->attributes = $this->mapAttributes($oxProduct);

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
        $aParams['oxarticles__oxvat'] = $sdkProduct->vat * 100;
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

        return array(); // todo implement: $oxProduct->getPictureGallery();
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
        $dimension = sprintf(
            '%sx%sx%s',
            $oxProduct->oxarticles__oxlength->value,
            $oxProduct->oxarticles__oxwidth->value,
            $oxProduct->oxarticles__oxheight->value
        );

        $attributes = array(
            Product::ATTRIBUTE_WEIGHT => $oxProduct->getWeight(),
            Product::ATTRIBUTE_VOLUME => $oxProduct->getSize(),
            Product::ATTRIBUTE_DIMENSION => $dimension,
            Product::ATTRIBUTE_UNIT => isset($this->oxidUnitMapper[$oxProduct->getUnitName()])
                ? $this->oxidUnitMapper[$oxProduct->getUnitName()]
                : self::DEFAULT_UNIT,
            // reference quantity is always 1 in oxid shop
            Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Product::ATTRIBUTE_QUANTITY => $oxProduct->getUnitQuantity(),
        );


        return $attributes;
    }
} 