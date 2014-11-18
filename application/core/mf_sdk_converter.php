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
        $sdkProduct->ean = $oxProduct->oxarticles__oxean->value;
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

        $sdkProduct->images = $this->mapImages($oxProduct);
        $sdkProduct->categories = $this->mapCategories($oxProduct);
        $sdkProduct->attributes = $this->mapAttributes($oxProduct);

        // articleUrl?
        // deliveryDate
        // deliveryWorkDays

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

        /** @var \oxConfig $oShopConfig */
        $oShopConfig = oxRegistry::get('oxConfig');
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency     = array_filter($currencyArray, function ($item, $sdkProduct) {
            return $item->unit === $sdkProduct->currency;
        });
        $currency = array_shift($currency);
        $rate = $currency->rate;

        $aParams['oxarticles__oxshopid'] = $sdkProduct->shopId;
        $aParams['oxarticles__oxean'] = $sdkProduct->ean;
        $aParams['oxarticles__oxexturl'] = $sdkProduct->url;
        $aParams['oxarticles__oxtitle'] = $sdkProduct->title;
        $aParams['oxarticles__oxshortdesc'] = $sdkProduct->shortDescription;

        // Price is netto or brutto depending on ShopConfig
        if (oxRegistry::get('oxConfig')->getConfigParam('blEnterNetPrice')) {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * $rate;
        } else {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * (1 + $sdkProduct->vat) * $rate;
        }
        $aParams['oxarticles__oxvat'] = $sdkProduct->vat * 100;
        $aParams['oxarticles__oxstock'] = $sdkProduct->availability;
        if (isset($sdkProduct->attributes[Product::ATTRIBUTE_UNIT])) {
            $aParams['oxarticles__oxunitname'] = $sdkProduct->attributes[Product::ATTRIBUTE_UNIT];
        }

        //attributes
        $aParams['oxarticles__oxunitname'] = $sdkProduct->attributes['unit'];
        $aParams['oxarticles__oxunitquantity'] = $sdkProduct->attributes['quantity'];
        $aParams['oxarticles__oxweight'] = $sdkProduct->attributes['weight'];

        $aDimension = explode('x', $sdkProduct->attributes['dimension']);
        $aParams['oxarticles__oxlength'] = $aDimension[0];
        $aParams['oxarticles__oxwidth'] = $aDimension[1];
        $aParams['oxarticles__oxheight'] = $aDimension[2];


        /**
         * Vendor: vendor name no use, only id can load vendor object
         * PurchasePrice has no equivalent in oxid
         * LongDescription not part of oxarticle but of oxartextends
         * Images
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
        $aImage = [];

        for ($i = 1; $i <= 12; $i++) {
            if ($oxProduct->{"oxarticles__oxpic$i"}->value) {
                $aImage[] = $oxProduct->getPictureUrl($i);
            }
        }

        return $aImage;
    }

    /**
     * @param oxarticle $oxProduct
     *
     * @return array
     */
    private function mapCategories($oxProduct)
    {
        $aCategory = [];

        $category = oxNew('oxcategory');
        $aIds = $oxProduct->getCategoryIds();


        foreach ($aIds as $id) {
            $category->load($id);

            $aCategory[] = $category->oxcategories__bepadocategory->value;
        }

        return $aCategory;
    }

    /**
     * @param oxarticle $oxProduct
     *
     * @return array
     */
    private function mapAttributes($oxProduct)
    {
        $sDimension = sprintf(
            '%sx%sx%s',
            $oxProduct->oxarticles__oxlength->value,
            $oxProduct->oxarticles__oxwidth->value,
            $oxProduct->oxarticles__oxheight->value
        );

        $aAttributes = array(
            Product::ATTRIBUTE_WEIGHT => $oxProduct->getWeight(),
            Product::ATTRIBUTE_VOLUME => (string) $oxProduct->getSize(),
            Product::ATTRIBUTE_DIMENSION => $sDimension,
            // reference quantity is always 1 in oxid shop
            Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Product::ATTRIBUTE_QUANTITY => $oxProduct->getUnitQuantity(),
        );

        // set optional unit
        if (isset($this->oxidUnitMapper[$oxProduct->getUnitName()])) {
            $attributes[Product::ATTRIBUTE_UNIT] = $this->oxidUnitMapper[$oxProduct->getUnitName()];
        }


        return $aAttributes;
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