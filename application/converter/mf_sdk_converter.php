<?php

use Bepado\SDK\Struct\Product;

class mf_sdk_converter implements mf_converter_interface
{
    const DEFAULT_PURCHASE_PRICE_CHAR = 'B';

    const OXID_DELIVERY_UNIT_WEEK = 'WEEK';

    const OXID_DELIVERY_UNIT_MONTH = 'MONTH';

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
        // Price are net or brut depending on ShopConfig
        $sdkProduct->price = $object->getPrice()->getNettoPrice();

        // create the purchase price with the matching mode
        $purchasePrice = new oxPrice();
        $purchasePrice->setVat($object->getArticleVat()) /100;
        if (oxRegistry::getConfig()->getConfigParam('blEnterNetPrice')) {
            $purchasePrice->setNettoPriceMode();
        } else {
            $purchasePrice->setBruttoPriceMode();
        }
        $purchasePrice->setPrice($object->{$this->computePurchasePriceField($object)}->value);
        $sdkProduct->purchasePrice = $purchasePrice->getNettoPrice();

        if (!$sdkProduct->purchasePrice) {
            $sdkProduct->purchasePrice = $sdkProduct->price;
        }
        $sdkProduct->currency = $currency->name;
        $sdkProduct->availability = $object->oxarticles__oxstock->value;

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

        file_put_contents("/tmp/changes", "Export-Categories: \n".serialize($sdkProduct->categories).PHP_EOL.PHP_EOL, FILE_APPEND);
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

        $aParams['oxarticles__oxartnum'] = $this->generateArtNum();
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
        $aUnitMapping = array_flip($this->oxidUnitMapper);
        if (isset($aUnitMapping[$object->attributes[Product::ATTRIBUTE_UNIT]])) {
            $aParams['oxarticles__oxunitname'] = $aUnitMapping[$object->attributes[Product::ATTRIBUTE_UNIT]];
        }
        $aParams['oxarticles__oxunitquantity'] = $object->attributes[Product::ATTRIBUTE_QUANTITY];
        $aParams['oxarticles__oxweight'] = $object->attributes[Product::ATTRIBUTE_WEIGHT];

        $aDimension = explode('x', $object->attributes[Product::ATTRIBUTE_DIMENSION]);
        $aParams['oxarticles__oxlength'] = $aDimension[0];
        $aParams['oxarticles__oxwidth'] = $aDimension[1];
        $aParams['oxarticles__oxheight'] = $aDimension[2];

        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');

        file_put_contents('/tmp/changes', "Import Images: \n".serialize($object->images).PHP_EOL.PHP_EOL, FILE_APPEND);
        file_put_contents('/tmp/changes', "Import Categories: \n".serialize($object->categories).PHP_EOL.PHP_EOL, FILE_APPEND);

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
        $aParams['oxarticles__oxdelivery'] = date('Y-m-d', $object->deliveryDate);
        // deliveryWorkDays
        $aParams['oxarticles__oxmaxdeltime'] = $object->deliveryWorkDays;
        $aParams['oxarticles__oxdeltimeunit'] = 'DAY';

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
        $purchaseGroupChar = $this->getVersionLayer()->getConfig()->getConfigParam('sPurchaseGroupChar');
        if (!in_array($purchaseGroupChar, array('A', 'B', 'C'))) {
            $purchaseGroupChar = self::DEFAULT_PURCHASE_PRICE_CHAR;
        }
        $purchaseGroupChar = strtolower($purchaseGroupChar);

        return 'oxarticles__oxprice'.$purchaseGroupChar;
    }

    private function generateArtNum()
    {

        return 'BEP-' . mt_rand(0,9999) . '-' . mt_rand(0,9999);
    }
}
