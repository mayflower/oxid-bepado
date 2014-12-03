<?php

use Bepado\SDK\Struct\Product;

class mf_sdk_converter //implements ProductConverter
{
    const DEFAULT_PURCHASE_PRICE_CHAR = 'A';
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
     * @param oxarticle $oxArticle
     *
     * @return Product
     */
    public function toBepadoProduct(oxarticle $oxArticle)
    {
        $sdkProduct = new Product();

        /** @var oxConfig $oShopConfig */
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency     = array_filter($currencyArray, function ($item) {
            return $item->rate === '1.00';
        });
        $currency = array_shift($currency);
        $sdkProduct->sourceId = $oxArticle->getId();
        $sdkProduct->ean = $oxArticle->oxarticles__oxean->value;
        $sdkProduct->url = $oxArticle->getLink();
        $sdkProduct->title = $oxArticle->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $oxArticle->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription = $oxArticle->getLongDescription()->getRawValue();

        // if no defined vendor, self is vendor
        if (null !== $oxArticle->getVendor()) {
            $sdkProduct->vendor = $oxArticle->getVendor()->oxvendor__oxtitle->value;
        } else {
            $oShop = $this->getVersionLayer()->createNewObject('oxshop');
            $oShop->load($oShopConfig->getShopId());
            $sdkProduct->vendor = $oShop->oxshops__oxname->value;
        }

        $sdkProduct->vat = $oxArticle->getArticleVat() / 100;
        // Price is net or brut depending on ShopConfig
        // @todo find the purchase representation in oxid article prices, defaults atm on net price
        $sdkProduct->price = $oxArticle->getPrice()->getNettoPrice();
        $purchasePrice = new oxPrice($oxArticle->{$this->computePurchasePriceField($oxArticle)}->value);
        $sdkProduct->purchasePrice = $purchasePrice->getNettoPrice();
        $sdkProduct->currency = $currency->name;
        $sdkProduct->availability = $oxArticle->oxarticles__oxstock->value;

        $sdkProduct->images = $this->mapImages($oxArticle);
        $sdkProduct->categories = $this->mapCategories($oxArticle);
        $sdkProduct->attributes = $this->mapAttributes($oxArticle);

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
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency = array_filter($currencyArray, function ($item) use ($sdkProduct) {
            return $item->name === $sdkProduct->currency;
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
        if ($this->getVersionLayer()->getConfig()->getConfigParam('blEnterNetPrice')) {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * $rate;
            $aParams[$this->computePurchasePriceField()] = $sdkProduct->purchasePrice * $rate;
        } else {
            $aParams['oxarticles__oxprice'] = $sdkProduct->price * (1 + $sdkProduct->vat) * $rate;
            $aParams[$this->computePurchasePriceField()] = $sdkProduct->purchasePrice * (1 + $sdkProduct->vat) * $rate;
        }
        $aParams['oxarticles__oxvat'] = $sdkProduct->vat * 100;
        $aParams['oxarticles__oxstock'] = $sdkProduct->availability;

        //attributes
        $aUnitMapping = array_flip($this->oxidUnitMapper);
        if (isset($aUnitMapping[$sdkProduct->attributes[Product::ATTRIBUTE_UNIT]])) {
            $aParams['oxarticles__oxunitname'] = $aUnitMapping[$sdkProduct->attributes[Product::ATTRIBUTE_UNIT]];
        }
        $aParams['oxarticles__oxunitquantity'] = $sdkProduct->attributes[Product::ATTRIBUTE_QUANTITY];
        $aParams['oxarticles__oxweight'] = $sdkProduct->attributes[Product::ATTRIBUTE_WEIGHT];

        $aDimension = explode('x', $sdkProduct->attributes[Product::ATTRIBUTE_DIMENSION]);
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
            Product::ATTRIBUTE_QUANTITY => $oxProduct->oxarticles__oxunitquantity->value,        # @todo need to be found
        );

        // set optional unit
        if (isset($this->oxidUnitMapper[$oxProduct->oxarticles__oxunitname->value])) {
            $aAttributes[Product::ATTRIBUTE_UNIT] = $this->oxidUnitMapper[$oxProduct->oxarticles__oxunitname->value];
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

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }

    /**
     * Depending on the module config
     *
     * @return string
     */
    private function computePurchasePriceField()
    {
        $purchaseGroupChar = $this->getVersionLayer()->getConfig()->getConfigParam('sPurchaseGroupChar');
        if (!in_array($purchaseGroupChar, array('A', 'B', 'C'))) {
            $purchaseGroupChar = self::DEFAULT_PURCHASE_PRICE_CHAR;
        }
        $purchaseGroupChar = strtolower($purchaseGroupChar);

        return 'oxarticles__ox'.$purchaseGroupChar.'price';
    }
} 