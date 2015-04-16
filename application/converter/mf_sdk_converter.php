<?php

/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

use Bepado\SDK\Struct\Product;

/**
 * Base converter, which converts oxArticles into SDK Products and back.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_converter implements mf_converter_interface
{
    /**
     * Default value for the purchase price chat.
     * This char decides which price should be chose for the purchase prise.
     */
    const DEFAULT_PURCHASE_PRICE_CHAR = 'B';

    /**
     * Value for the delivery unit week.
     */
    const OXID_DELIVERY_UNIT_WEEK = 'WEEK';

    /**
     * Value for the delivery unit week.
     */
    const OXID_DELIVERY_UNIT_MONTH = 'MONTH';

    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * Little mapper for the units.
     *
     * @var array
     */
    private $oxidUnitMapper = array();

    /**
     * @var mf_module_helper
     */
    private $moduleHelper;

    public function __construct()
    {
        $result = $this->getVersionLayer()->getDb(true)->getAll('SELECT * FROM mfbepadounits');
        foreach ($result as $row) {
            $this->oxidUnitMapper[$row['OXID']] = $row['BEPADOUNITKEY'];
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param oxarticle $object
     *
     * @return Product
     */
    public function fromShopToBepado($object)
    {
        $sdkProduct = new Product();

        /** @var oxConfig $oShopConfig */
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency     = array_filter($currencyArray, function ($item) {
            return $item->rate === '1.00';
        });
        $currency = array_shift($currency);
        $sdkProduct->sourceId = $object->getId();
        $sdkProduct->ean = $object->oxarticles__oxean->value;
        $sdkProduct->url = $object->getLink();
        $sdkProduct->title = $object->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $object->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription = $object->getLongDescription()->getRawValue();

        // if no defined vendor, self is vendor
        if (null !== $object->getVendor()) {
            $sdkProduct->vendor = $object->getVendor()->oxvendor__oxtitle->value;
        } else {
            $oShop = $this->getVersionLayer()->createNewObject('oxshop');
            $oShop->load($oShopConfig->getShopId());
            $sdkProduct->vendor = $oShop->oxshops__oxname->value;
        }

        $sdkProduct->vat = $object->getArticleVat() / 100;
        $sdkProduct->price = $this->getModuleHelper()->createNetPrice($object->getPrice());

        // create the purchase price with the matching mode
        $purchasePrice = new oxPrice();
        $purchasePrice->setVat($object->getArticleVat()) /100;

        $purchasePrice->setPrice($object->{$this->computePurchasePriceField($object)}->value);
        $sdkProduct->purchasePrice = $this->getModuleHelper()->createNetPrice($purchasePrice);

        if (!$sdkProduct->purchasePrice) {
            $sdkProduct->purchasePrice = $sdkProduct->price;
        }
        $sdkProduct->currency = $currency->name;
        $sdkProduct->availability = (int) $object->oxarticles__oxstock->value;

        $sdkProduct->images = $this->mapImages($object);
        $sdkProduct->categories = $this->mapCategories($object);
        $sdkProduct->attributes = $this->mapAttributes($object);

        // deliveryDate
        $deliveryDate = DateTime::createFromFormat('Y-m-j H:i:s', $object->getFieldData('oxdelivery').' 00:00:00');
        $deliveryDateTimestamp = $deliveryDate->getTimestamp();
        if ($deliveryDateTimestamp > microtime(true)) {
            $sdkProduct->deliveryDate = $deliveryDateTimestamp;
        }

        // deliveryWorkDays
        $maxDeliveryTime = (int) $object->getFieldData('oxmaxdeltime');
        $deliveryUnit = $object->getFieldData('oxdeltimeunit');

        switch ($deliveryUnit) {
            case self::OXID_DELIVERY_UNIT_MONTH:
                $deliveryUnit = 20;
                break;
            case self::OXID_DELIVERY_UNIT_WEEK:
                $deliveryUnit = 5;
                break;
            default:
                $deliveryUnit = 1;
        }
        $sdkProduct->deliveryWorkDays = $maxDeliveryTime * $deliveryUnit;

        return $sdkProduct;
    }

    /**
     * {@inheritDoc}
     *
     * @param Product $object
     *
     * @return oxarticle
     */
    public function fromBepadoToShop($object)
    {
        /** @var mf_sdk_logger_helper $logger */
        $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');

        /** @var oxarticle $oxProduct */
        $oxProduct = oxNew('oxarticle');
        $aParams = [];

        /** @var \oxConfig $oShopConfig */
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $currencyArray = $oShopConfig->getCurrencyArray();

        $currency = array_filter($currencyArray, function ($item) use ($object) {
            return $item->name === $object->currency;
        });
        $currency = array_shift($currency);
        $rate = $currency->rate;

        $aParams['oxarticles__oxean'] = $object->ean;
        $aParams['oxarticles__oxexturl'] = $object->url;
        $aParams['oxarticles__oxtitle'] = $object->title;
        $aParams['oxarticles__oxshortdesc'] = $object->shortDescription;

        // Price is netto or brutto depending on ShopConfig
        // PurchasePrice has no equivalent in oxid
        if (oxRegistry::getConfig()->getConfigParam('blEnterNetPrice')) {
            $aParams['oxarticles__oxprice'] = $object->price * $rate;
            $aParams[$this->computePurchasePriceField()] = $object->purchasePrice * $rate;
        } else {
            $aParams['oxarticles__oxprice'] = $object->price * (1 + $object->vat) * $rate;
            $aParams[$this->computePurchasePriceField()] = $object->purchasePrice * (1 + $object->vat) * $rate;
        }
        $aParams['oxarticles__oxvat'] = $object->vat * 100;
        $aParams['oxarticles__oxstock'] = $object->availability;

        //attributes
        if (isset($object->attributes[Product::ATTRIBUTE_UNIT])) {
            $aUnitMapping = array_flip($this->oxidUnitMapper);
            if (isset($aUnitMapping[$object->attributes[Product::ATTRIBUTE_UNIT]])) {
                $aParams['oxarticles__oxunitname'] = $aUnitMapping[$object->attributes[Product::ATTRIBUTE_UNIT]];
            }
        }

        $aParams['oxarticles__oxunitquantity'] = $object->attributes[Product::ATTRIBUTE_QUANTITY];
        $aParams['oxarticles__oxweight'] = $object->attributes[Product::ATTRIBUTE_WEIGHT];

        $aDimension = explode('x', $object->attributes[Product::ATTRIBUTE_DIMENSION]);
        $aParams['oxarticles__oxlength'] = $aDimension[0];
        $aParams['oxarticles__oxwidth'] = $aDimension[1];
        $aParams['oxarticles__oxheight'] = $aDimension[2];

        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');

        foreach ($object->images as $key => $imagePath) {
            if ($key < 12){
                try {
                    list($fieldName, $fieldValue) = $sdkHelper->createOxidImageFromPath($imagePath, $key+1);
                    $aParams[$fieldName] = $fieldValue;
                } catch (\Exception $e) {
                    $logger->writeBepadoLog(
                        sprintf(
                            'Image %s could not be saved during product conversion. Got the following exception: %s',
                            $imagePath,
                            $e->getMessage()
                        )
                    );
                }
            }
        }

        // deliveryDate
        if (null !== $object->deliveryDate) {
            $aParams['oxarticles__oxdelivery'] = date('Y-m-d', $object->deliveryDate);
        }

        // deliveryWorkDays
        if (null !== $object->deliveryWorkDays) {
            $aParams['oxarticles__oxmaxdeltime'] = $object->deliveryWorkDays;
            $aParams['oxarticles__oxdeltimeunit'] = 'DAY';
        }

        // Vendor: vendor name no use, only id can load vendor object
        // Category: category name no use id can load category object

        $oxProduct->assign($aParams);
        $oxProduct->setArticleLongDesc($object->longDescription);

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
        $aIds = $oxProduct->getCategoryIds();

        $bepadoCategories = $this->getVersionLayer()->createNewObject('oxlist');
        $bepadoCategories->init('oxbase', 'bepado_categories');
        $bepadoCategories->getBaseObject();
        $bepadoCategories->getList();
        $bepadoCategories = $bepadoCategories->getArray();

        foreach ($aIds as $oxidCategoryId) {
            $matchingCategory = array_filter($bepadoCategories, function($category) use ($oxidCategoryId) {
                return $category->getFieldData('bepado_categories__catnid') == $oxidCategoryId;
            });
            if (count($matchingCategory) === 1) {
                $category = array_shift($matchingCategory);
                $aCategory[] = $category->bepado_categories__title->rawValue;
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
}
