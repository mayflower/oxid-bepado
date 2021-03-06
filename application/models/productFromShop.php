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

use Bepado\SDK\ProductFromShop;
use Bepado\SDK\SDK;
use Bepado\SDK\SecurityException;
use Bepado\SDK\Struct\Address;
use Bepado\SDK\Struct\Order;
use Bepado\SDK\Struct\OrderItem;
use Bepado\SDK\Struct\Product;

/**
 * Implementation of one of the base SDK models.
 *
 * Is needed for the bepado communication from the own shop to bepado.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class oxidProductFromShop implements ProductFromShop
{
    /**
     * @var SDK
     */
    private $sdk;

    /**
    * @var VersionLayerInterface
    */
    private $_oVersionLayer;

    const BEPADO_USERGROUP_ID = 'bepadoshopgroup';

    /**
     * @param array $ids
     *
     * @return array|Product[]
     */
    public function getProducts(array $ids)
    {
        $sdkProducts = array();
        /** @var mfProductConverter $oModuleSDKConverter */
        $oModuleSDKConverter = $this->getVersionLayer()->createNewObject('mfProductConverterChain');
        /** @var mf_sdk_article_helper $articleHelper */
        $articleHelper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');
        foreach ($ids as $id) {
            /** @var oxarticle $oxArticle */
            $oxArticle = $this->getVersionLayer()->createNewObject('oxarticle');
            $oxArticle->load($id);

            if (!$oxArticle->isLoaded() || !$articleHelper->isArticleExported($oxArticle)) {
                continue;
            }

            $sdkProduct = new Product();
            $oModuleSDKConverter->fromShopToBepado($oxArticle, $sdkProduct);
            $sdkProducts[] = $sdkProduct;
        }

        return $sdkProducts;
    }

    /**
     * @throws \BadMethodCallException
     *
     * @return string[]|void
     */
    public function getExportedProductIDs()
    {
        $ids = array();
        $sql = "SELECT p_source_id FROM mfbepadoproducts WHERE state = ".mfBepadoConfiguration::ARTICLE_STATE_EXPORTED;
        $list = $this->getVersionLayer()->getDb(true)->execute($sql);

        while (!$list->EOF) {
            $ids[] = $list->fields['p_source_id'];
            $list->MoveNext();
        }

        return $ids;
    }

    /**
     * Oxid does not support reservations at all, so we will just check the order
     * and validates its stocks.
     *
     * @param Order $order
     *
     * @throws Exception
     */
    public function reserve(Order $order)
    {
        /** @var oxBasket $oxBasket */
        $oxBasket = $this->_oVersionLayer->createNewObject('oxbasket');
        $this->addToBasket($order->orderItems, $oxBasket);
        if (!$oxBasket->getProductsCount()) {
            throw new Exception('No valid products in basket');
        }

        /** @var oxOrder $oxOrder */
        $oxOrder = $this->_oVersionLayer->createNewObject('oxorder');
        $oxOrder->validateStock($oxBasket);
    }

    /**
     * @return SDK
     */
    public function getOrCreateSDK()
    {
        if (null === $this->sdk) {
            $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
            $this->sdk = $sdkHelper->instantiateSdk();
        }

        return $this->sdk;
    }

    /**
     * Bepado sends an order object. Depending on that we
     * will fetch the products from its OrderItems, create a user
     * out for the remote shop.
     *
     * With the mapped payment method and the shipping costs a basket
     * will be created which will finalize an order inside of oxid.
     *
     * @param Order $order
     *
     * @throws Exception
     *
     * @return string
     */
    public function buy(Order $order)
    {
        /** @var oxBasket $oxBasket */
        $oxBasket = $this->getVersionLayer()->createNewObject('oxbasket');
        $shopUser = $this->getOrCreateUser($order);
        $this->addToBasket($order->orderItems, $oxBasket);
        if (!$oxBasket->getProductsCount()) {
            throw new Exception('No valid products in basket');
        }

        $oxBasket->setPayment(mfBepadoConfiguration::DEFAULT_PAYMENT_TYPE);
        $shippingOrder = clone $order;
        $shippingCosts = $this->getOrCreateSDK()->calculateShippingCosts($shippingOrder);

        /** @var oxPrice $oxPrice with the decision for the price mode*/
        $oxPrice = $this->getVersionLayer()->createNewObject('oxprice');
        if (0 === $shippingCosts->shippingCosts) {
            $oxPrice->setPrice(0);
        } else {
            $oxPrice->setPrice(
                $shippingCosts->shippingCosts,
                ((100*$shippingCosts->grossShippingCosts/$shippingCosts->shippingCosts))/100 - 1
            );
        }
        $oxBasket->setCost('oxdelivery', $oxPrice);

        /** @var oxOrder $oxOrder */
        $oxOrder = $this->getVersionLayer()->createNewObject('oxorder');
        $iSuccess = $oxOrder->finalizeOrder($oxBasket, $shopUser);
        $shopUser->onOrderExecute($oxBasket, $iSuccess);
        $this->cleanUp();

        return $oxOrder->getId();
    }

    /**
     * Oxid and Bepado got two different address types: delivery and bill. both got an
     * almost equal structure. So we just transform them in here.
     *
     * @param Address $address
     * @param string  $type
     *
     * @return array
     */
    private function convertAddress(Address $address, $type = 'oxaddress__ox')
    {
        $oxCountry = oxNew('oxcountry');
        $select = $oxCountry->buildSelectString(array('OXISOALPHA3' => $address->country, 'OXACTIVE' => 1));
        $countryID = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oxCountryId = $countryID ?: null;

        $oxState = oxNew('oxstate');
        $select = $oxState->buildSelectString(array('OXTITLE' => $address->state));
        $stateID = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oxStateId = $stateID ?: null;

        $oxAddress = array(
            $type.'company'   => $address->company,
            $type.'fname'     => $address->firstName.(
                strlen($address->middleName) > 0 ? ' '.$address->middleName : ''
                ),
            $type.'lname'      => $address->surName,
            $type.'street'    => $address->street,
            $type.'streetnr'  => $address->streetNumber,
            $type.'addinfo'   => $address->additionalAddressLine,
            $type.'ustid'     => null,
            $type.'city'      => $address->city,
            $type.'countryid' => $oxCountryId,
            $type.'stateid'   => $oxStateId,
            $type.'zip'       => $address->zip,
            $type.'fax'       => null,
            $type.'sal'       => null,
            $type.'fon'       => $address->phone,
            $type.'email'     => $address->email,
        );

        return $oxAddress;
    }

    /**
     * By using the convertet this method creates a set of oxArticles.
     *
     * @param OrderItem[]|array $orderItems
     * @param oxBasket $oxBasket
     *
     * @throws Exception
     * @throws null
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     * @throws oxOutOfStockException
     *
     * @return array|oxArticle[]
     */
    private function addToBasket(array $orderItems, oxBasket $oxBasket)
    {
        foreach ($orderItems as $item) {
            $oxBasket->addToBasket($item->product->sourceId, $item->count);
        }

        $oxBasket->calculateBasket(true);
    }

    /**
     * When a shop is registered as an user it will be fetched from database, created if not.
     * The bepado bill address will be used as the users address, the delivery address added to the user.
     *
     * @param Order $order
     *
     * @return oxUser
     */
    private function getOrCreateUser(Order $order)
    {
        $shopId = $order->orderShop;
        $userName = $order->billingAddress->email;

        $shop = $this->getOrCreateSDK()->getShop($shopId);

        if (!$shop) {
            throw new SecurityException(sprintf('Shop with id %s not known', $shopId));
        }

        $oxGroup = $this->_oVersionLayer->createNewObject('oxgroups');
        if (!$oxGroup->load(self::BEPADO_USERGROUP_ID)) {
            throw new \RuntimeException('No user group for bepado remote shop found.');
        }

        /** @var oxUser $shopUser */
        $shopUser = $this->_oVersionLayer->createNewObject('oxuser');
        $selectQuery = $shopUser->buildSelectString(array('OXUSERNAME' => $userName, 'OXACTIVE' => 1));

        $userId = $this->getVersionLayer()->getDb(true)->getOne($selectQuery);
        if ($userId) {
            $shopUser->load($userId);
        }

        // creates the shop as an user if it does not exist
        if (!$shopUser->isLoaded()) {
            $values = array(
                'oxuser__oxusername'   => $userName,
                'oxuser__oxurl'        => $shop->url,
                'oxuser__oxactive'     => true,
            );
            $values = array_merge($values, $this->convertAddress($order->billingAddress, 'oxuser__ox'));
            $shopUser->assign($values);
            $shopUser->addToGroup(self::BEPADO_USERGROUP_ID);

            $shopUser->save();
        }

        // assign the delivery address to the shop user
        $deliveryAddress = $this->convertAddress($order->deliveryAddress);
        $oxDeliveryAddress = oxNew('oxaddress');
        $oxDeliveryAddress->assign($deliveryAddress);
        $oxDeliveryAddress->oxaddress__oxuserid = new oxField($shopUser->getId(), oxField::T_RAW);
        $oxDeliveryAddress->oxaddress__oxcountry = $shopUser->getUserCountry($deliveryAddress['oxaddress__oxcountry']);
        $oxDeliveryAddress->save();
        $_POST['sDeliveryAddressMD5'] = $shopUser->getEncodedDeliveryAddress().$oxDeliveryAddress->getEncodedDeliveryAddress();
        $_POST['deladrid'] = $oxDeliveryAddress->getId();

        return $shopUser;
    }

    /**
     * Some clean ups after the buy process
     */
    private function cleanUp()
    {
        $this->getVersionLayer()->getSession()->delBasket();
        unset($_POST['sDeliveryAddressMD5'], $_POST['deladrid']);
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
}

