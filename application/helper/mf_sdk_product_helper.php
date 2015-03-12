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

use Bepado\SDK\SDK;
use Bepado\SDK\Struct as Struct;
use Bepado\SDK\Struct\Reservation;

/**
 * Helper class for all (oxid) article (bepado) product communication and interactions.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_product_helper extends mf_abstract_helper
{
    /**
     * @var SDK
     */
    protected $sdk;

    /**
     * @param oxBasket $oxBasket
     */
    public function checkProductsInBasket(oxBasket $oxBasket)
    {
        /** @var  oxBasketItem[] $aBasket */
        $aBasket = $oxBasket->getContents();
        $countChanges = 0;

        foreach ($aBasket as $basketItem) {
            /** @var mf_bepado_oxarticle $oxBasketArticle */
            $oxBasketArticle = $basketItem->getArticle();
            $amount = $basketItem->getAmount();
            $errorMsg = [];
            $basketItem->bepado_check = new oxField('', oxField::T_TEXT);
            $changedAvailability = null;
            $changedPrice = null;

            /** @var mf_sdk_article_helper $helper */
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');

            if (!$helper->isArticleImported($oxBasketArticle)) {
                continue;
            }

            $product = $helper->computeSdkProduct($oxBasketArticle);
            foreach ($this->doCheckProduct($product) as $message) {
                if (isset($message->values['availability'])) {
                    $changedAvailability = $message->values['availability'];
                } elseif (isset($message->values['price'])) {
                    $changedPrice = $message->values['price'];
                }
            }

            if (null !== $changedAvailability && $amount > $changedAvailability) {
                if ($changedAvailability != 0) {
                    $errorMsg[] = 'This product is available only '.$changedAvailability.' time'
                        .($changedAvailability == 1 ? '.' : 's.').' Either delete the
                        product from your basket or purchase the reduced amount.';
                } else {
                    $errorMsg[] = 'This product is not available at the moment.';
                }
                $basketItem->setAmount($changedAvailability);
            }

            if (null !== $changedPrice) {
                $basketItem->setPrice(new oxPrice($changedPrice));
                $errorMsg[] = 'The price has changed.';
            }

            if ($errorMsg) {
                $countChanges++;
                $basketItem->bepado_check = new oxField(
                    '<ul><li><i>' . implode('</i></li><li><i>', $errorMsg) . '</i></li></ul>',
                    oxField::T_TEXT
                );
            }

        }

        // do calculate when there where changes only
        if ($countChanges > 0) {
            $oxBasket->calculateBasket(true);
        }
    }

    /**
     * @param Struct\Product $sdkProduct
     *
     * @return Struct\Message[]
     */
    private function doCheckProduct($sdkProduct)
    {
        /** @var mf_sdk_logger_helper $logger */
        $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');
        $results = [];

        try {
            $result = $this->getSdk()->checkProducts(array($sdkProduct));
            if (is_array($result)) {
                foreach ($result as $id => $messages) {
                    $results = array_merge($results, $messages);
                }
            }
        } catch (\Exception $e) {
            $errMsg = new Struct\Message(array(
                'message' => 'Problem while checking the product with: "%exception"',
                'values'  => array('exception' => $e->getMessage())
            ));

            $logger->writeBepadoLog(str_replace('%exception', $e->getMessage(), $errMsg->message));
            $results[] = $errMsg;
        }

        return $results;
    }

    /**
     * Does the reservation work on the sdk. It throws the exception the
     * order checks while finalizing a order.
     *
     * @param oxOrder $oxOrder
     *
     * @param oxUser $oxUser
     * @return Reservation|bool
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     * @throws oxOutOfStockException
     */
    public function reserveProductsInOrder(oxOrder $oxOrder, oxUser $oxUser)
    {
        /** @var mf_sdk_order_converter $converter */
        $converter = $this->getVersionLayer()->createNewObject('mf_sdk_order_converter');
        /** @var mf_sdk_address_converter $addressConverter */
        $addressConverter = $this->getVersionLayer()->createNewObject('mf_sdk_address_converter');

        $sdkOrder = $converter->fromShopToBepado($oxOrder);
        if (null == $sdkOrder->deliveryAddress || null == $sdkOrder->deliveryAddress->firstName) {
            $sdkOrder->deliveryAddress = $addressConverter->fromShopToBepado($oxUser, 'oxuser__ox');
        }

        if (null == $sdkOrder->billingAddress || null == $sdkOrder->billingAddress->firstName) {
            // todo use a persisted (session -> delivery id) address when exists
            $sdkOrder->billingAddress = $addressConverter->fromShopToBepado($oxUser, 'oxuser__ox');
        }

        if (count($sdkOrder->orderItems) === 0) {
            return false;
        }

        $reservation = $this->getSdk()->reserveProducts($sdkOrder);

        if (!$reservation instanceof Reservation) {
            $exception = new oxNoArticleException();
            $exception->setMessage('Expected \Bepado\SDK\Struct\Reservation got '.get_class($reservation));
            throw $exception;
        }
        if (!$reservation->success) {
            $computedMessages = array();
            foreach ($reservation->messages as $shopId => $messages) {
                foreach ($messages as $message) {
                    $keys = array();
                    foreach ($message->values as $key => $values) {
                        $keys[] = '%'.$key;
                    }
                    $computedMessages[] = str_replace($keys, $message->values, $message->message);
                }
            }

            throw new \Exception(sprintf('Errors while reservation: %s', implode(', ', $computedMessages)));
        }

        return $reservation;
    }

    /**
     * @param Reservation $reservation
     * @param oxOrder
     *
     * @return bool[]
     */
    public function checkoutProducts(Reservation $reservation, oxOrder $oxOrder)
    {
        $result = $this->getSdk()->checkout($reservation, $oxOrder->getId());

        return $result;
    }

    /**
     * @return SDK
     */
    private function getSdk()
    {
        if (null === $this->sdk) {
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
            $config = $helper->createSdkConfigFromOxid();
            $this->sdk = $helper->instantiateSdk($config);
        }

        return $this->sdk;
    }
}
