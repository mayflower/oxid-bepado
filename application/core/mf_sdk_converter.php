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
        $sdkProduct->url = $oxProduct->getLink();
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

        $currency = array_filter($currencyArray, function ($item, $sdkProduct) {
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
        // PurchasePrice has no equivalent in oxid
        if (oxRegistry::get('oxConfig')->getConfigParam('blEnterNetPrice')) {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * $rate;
        } else {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * (1 + $sdkProduct->vat) * $rate;
        }
        $aParams['oxarticles__oxvat'] = $sdkProduct->vat * 100;
        $aParams['oxarticles__oxstock'] = $sdkProduct->availability;

        //attributes
        $aUnitMapping = array_flip($this->oxidUnitMapper);
        if (isset($aUnitMapping[$sdkProduct->attributes[Product::ATTRIBUTE_UNIT]])) {
            $aParams['oxarticles__oxunitname'] = $aUnitMapping[$sdkProduct->attributes[Product::ATTRIBUTE_UNIT]];
        }
        $aParams['oxarticles__oxunitquantity'] = $sdkProduct->attributes['quantity'];
        $aParams['oxarticles__oxweight'] = $sdkProduct->attributes['weight'];

        $aDimension = explode('x', $sdkProduct->attributes['dimension']);
        $aParams['oxarticles__oxlength'] = $aDimension[0];
        $aParams['oxarticles__oxwidth'] = $aDimension[1];
        $aParams['oxarticles__oxheight'] = $aDimension[2];

        foreach ($sdkProduct->images as $key => $imagePath) {
            if ($key < 12){
                $aImagePath = explode('/', $imagePath);
                $sImageName = $aImagePath[(count($aImagePath) - 1)];
                $aParams['oxarticles__oxpic' . ($key + 1)] = $sImageName;

                copy($imagePath, $oShopConfig->getMasterPictureDir() . 'product/' . ($key + 1) . '/' . $sImageName);
            }
        }

        // Vendor: vendor name no use, only id can load vendor object
        // Category: category name no use id can load category object

        $oxProduct->assign($aParams);
        $oxProduct->setArticleLongDesc($sdkProduct->longDescription);

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

        $oCat = oxNew('oxlist');
        $oCat->init('oxbase', 'bepado_categories');
        $oCat->getBaseObject();
        $oCat->getList();
        $oCat = $oCat->getArray();

        foreach ($aIds as $id) {
            if (array_key_exists($id, $oCat)) {
                $aCategory[] = $oCat[$id]->bepado_categories__title->rawValue;
            }
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
        $size = $oxProduct->oxarticles__oxlength->value *
            $oxProduct->oxarticles__oxwidth->value *
            $oxProduct->oxarticles__oxheight->value;

        $aAttributes = array(
            Product::ATTRIBUTE_WEIGHT => $oxProduct->oxarticles__oxweight->value,
            Product::ATTRIBUTE_VOLUME => (string) $size,
            Product::ATTRIBUTE_DIMENSION => $sDimension,
            // reference quantity is always 1 in oxid shop
            Product::ATTRIBUTE_REFERENCE_QUANTITY => 1,
            Product::ATTRIBUTE_QUANTITY => $oxProduct->oxarticles__oxunitquantity->value,
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