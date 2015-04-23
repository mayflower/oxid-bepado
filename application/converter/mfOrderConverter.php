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

use Bepado\SDK\Struct as Struct;

/**
 * Converter class to transport the oxid order data into a sdk order object and back.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mfOrderConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * @var mfAddressConverter
     */
    protected $addressConverter;

    /**
     * @var mfProductConverter
     */
    protected $productConverter;

    public function __construct()
    {
        $this->addressConverter = $this->getVersionLayer()->createNewObject('mfAddressConverter');
        $this->productConverter = $this->getVersionLayer()->createNewObject('mfProductConverterChain');
    }

    /**
     * {@inheritDoc}
     *
     * Creates a bepado order object from the information given by a oxOrder.
     *
     * @param oxOrder      $shopObject
     * @param Struct\Order $bepadoObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject, $parameters = array())
    {
        $address = new Struct\Address();
        $bepadoObject->billingAddress = $this->addressConverter->fromShopToBepado($shopObject, $address, 'oxorder__oxbill');
        $address = new Struct\Address();
        $bepadoObject->deliveryAddress = $this->addressConverter->fromShopToBepado($shopObject, $address, 'oxorder__oxdel');

        if (!$bepadoObject->deliveryAddress->firstName) {
            $bepadoObject->deliveryAddress = $bepadoObject->billingAddress;
        }

        $bepadoObject->localOrderId = $shopObject->getId();
        $bepadoObject->paymentType = $this->createSDKPaymentType($shopObject);
        $bepadoObject->orderItems = $this->createOrderItems($shopObject);

        foreach ($parameters as $property => $value) {
            $bepadoObject->$property = $value;
        }
    }

    /**
     * {@inheritDoc}
     *
     * Things we won't ever convert here:
     *  - userId, cause this one will be created while checking out in the local shop
     *  - all delivery address stuff, cause it is created out of the user details
     *
     * @param Struct\Order $bepadoObject
     * @param oxOrder      $shopObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {
        $parameters = array('oxorder__oxid' => null);
        $parameters = array_merge($parameters, $this->addressConverter->fromBepadoToShop($bepadoObject->billingAddress, $shopObject, 'oxorder__oxbill'));
        $parameters['oxorder__oxpaymentid'] = $this->createOxidPaymentId($bepadoObject);
        $shopObject->assign($parameters);
    }

    /**
     * Create the Payment type from the order' payment id.
     * We created a mapping for that reason.
     *
     * @param oxOrder $object
     *
     * @return string
     */
    private function createSDKPaymentType(oxOrder $object)
    {
        $oxPayment = $this->getVersionLayer()->createNewObject('oxpayment');
        $oxPayment->load($object->oxorder__oxpaymenttype->value);

        return $oxPayment->isLoaded() && null !== $oxPayment->getFieldData('bepadopaymenttype')
            ? $oxPayment->getFieldData('bepadopaymenttype')
            : Struct\Order::PAYMENT_UNKNOWN;
    }

    /**
     * Creates the list of order items from the oxid basket items.
     *
     * @param OxOrder $object
     * @return array
     */
    private function createOrderItems(OxOrder $object)
    {
        $orderItems = array();

        /** @var oxList $orderArticles */
        $orderArticles = $object->getOrderArticles();

        /** @var mf_sdk_article_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');

        foreach ($orderArticles->getArray() as $orderArticle) {
            if (!$helper->isOrderArticleImported($orderArticle)) {
                continue;
            }
            $article = $orderArticle->getArticle();
            $orderItem = new Struct\OrderItem();
            $orderItem->product = $helper->computeSdkProduct($article);
            $orderItem->count = (int) $orderArticle->getFieldData('oxorderarticles__oxamount');
            $orderItems[] = $orderItem;
        }

        return $orderItems;
    }

    /**
     * @param Struct\Order $order
     *
     * @return null
     */
    private function createOxidPaymentId(Struct\Order $order)
    {
        $oxPayment = $this->_oVersionLayer->createNewObject('oxpayment');
        $select = $oxPayment->buildSelectString(array('bepadopaymenttype' => $order->paymentType));
        $paymentID = $this->_oVersionLayer->getDb(true)->getOne($select);

        return $paymentID ?: null;
    }
}
