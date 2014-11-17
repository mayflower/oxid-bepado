<?php

use Bepado\SDK\Struct\Product;

class mf_sdk_converter //implements ProductConverter
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    private $oxidUnitMapper = array(
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
        '_UNIT_ITEM' => 'piece',
    );

    /**
     * @param oxarticle $oxProduct
     *
     * @return Product
     */
    public function toBepadoProduct(oxarticle $oxProduct)
    {
        $sdkProduct = new Product();

        /** @var oxConfig $oShopConfig */
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency     = array_filter($currencyArray, function ($item) {
            return $item->rate === '1.00';
        });
        $currency = array_shift($currency);

        $sdkProduct->sourceId = $oxProduct->getId();
        $sdkProduct->title = $oxProduct->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $oxProduct->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription = $oxProduct->getLongDescription()->getRawValue();

        $oShop = oxNew('oxshop');
        $oShop->load($oShopConfig->getShopId());

        // if no defined vendor, self is vendor
        $vendorName = $oxProduct->getVendor()->oxvendor__oxtitle->value;
        if ($vendorName) {
            $sdkProduct->vendor = $vendorName;
        } else {
            $sdkProduct->vendor = $oShop->oxshops__oxname->value;
        }

        $sdkProduct->vat = $oxProduct->getArticleVat() / 100;
        // Price is netto or brutto depending on ShopConfig
        // PurchasePrice has no equivalent in oxid so netto price is taken
        $priceValue = (float) $oxProduct->oxarticles__oxprice->value;
        if ($oShopConfig->getConfigParam('blEnterNetPrice')) {
            $sdkProduct->price = $priceValue  * (1 + $sdkProduct->vat);
            $sdkProduct->purchasePrice = $priceValue;
        } else {
            $sdkProduct->price = $priceValue;
            $sdkProduct->purchasePrice = $priceValue / (1 + $sdkProduct->vat);
        }
        $sdkProduct->currency = $currency->name;
        $sdkProduct->availability = $oxProduct->oxarticles__oxstock->value;

        $sdkProduct->categories = $oxProduct->getCategory()->oxcategories__bepadocategory->value;
        $sdkProduct->attributes = $this->mapAttributes($oxProduct);

        /**               not fully implemented yet               */
        $sdkProduct->images = $this->mapImages($oxProduct);

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

        // Price is netto or brutto depending on ShopConfig
        if ($this->getVersionLayer()->getConfig()->getConfigParam('blEnterNetPrice')) {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price;
        } else {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * (1 + $sdkProduct->vat);
        }
        $aParams['oxarticles__oxvat'] = $sdkProduct->vat * 100;
        $aParams['oxarticles__oxstock'] = $sdkProduct->availability;
        if (isset($sdkProduct->attributes[Product::ATTRIBUTE_UNIT])) {
            $aParams['oxarticles__oxunitname'] = $sdkProduct->attributes[Product::ATTRIBUTE_UNIT];
        }

        /**
         * Vendor: vendor name no use, only id can load vendor object
         * PurchasePrice has no equivalent in oxid
         * Currency: unit won't initialize currency object
         * Images
         * Attributes
         * Category: category name no use id can load category object
         */

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
        // return $oxProduct->getPictureGallery();

        return array(); // todo implement
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
            Product::ATTRIBUTE_VOLUME => (string) $oxProduct->getSize(),
            Product::ATTRIBUTE_DIMENSION => $dimension,
            // reference quantity is always 1 in oxid shop
            Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Product::ATTRIBUTE_QUANTITY => $oxProduct->getUnitQuantity(),
        );

        // set optional unit
        if (isset($this->oxidUnitMapper[$oxProduct->getUnitName()])) {
            $attributes[Product::ATTRIBUTE_UNIT] = $this->oxidUnitMapper[$oxProduct->getUnitName()];
        }


        return $attributes;
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
} 