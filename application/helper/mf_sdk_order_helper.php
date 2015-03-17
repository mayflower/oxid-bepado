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
use Bepado\SDK\Struct\Message;
use Bepado\SDK\Struct\OrderStatus;

/**
 * This helper will support the work with orders.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_order_helper extends mf_abstract_helper
{
    /**
     * @var SDK
     */
    protected $sdk;

    /**
     * Computes an order contains exported articles and takes care on state updates.
     *
     * @param $oOrder
     * @param null|bool $deleted
     */
    public function checkForOrderStateUpdates($oOrder, $deleted = null)
    {
       if ($this->hasExportedArticles($oOrder)) {
            $this->doUpdateOrderState($oOrder, $deleted);
       }
    }

    /**
     * Just one exported article is enough to decide whether an order needs to be checked for
     * order state updates or not.
     *
     * @param oxOrder $oOrder
     * @return bool
     */
    private function hasExportedArticles(oxOrder $oOrder)
    {
        /** @var mf_sdk_article_helper $helper */
        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');

        foreach ($oOrder->getOrderArticles() as $oItem) {
            $oArticle = $oItem->getArticle();
            if ($helper->isArticleExported($oArticle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * OrderStatus Id = Order providerOrderId = oxorder Id
     *
     * @param oxOrder  $oOrder
     * @param int|null $deleted
     */
    private function doUpdateOrderState(oxOrder $oOrder, $deleted)
    {
        $orderStatus = new OrderStatus();
        $orderStatus->id = $oOrder->getId();
        $orderStatus->status = OrderStatus::STATE_OPEN;

        $message = new Message();
        if ($deleted || $oOrder->getFieldData('oxstorno')) {
            $orderStatus->status = OrderStatus::STATE_CANCELED;
            $message->message = 'Provider shop canceled the order';
        } elseif ($this->deliveryDataWasJustSet()) {
            $orderStatus->status = OrderStatus::STATE_DELIVERED;
            $message->message = 'Provider shop has processed and delivered order on %senddate.';
            $message->values['senddate'] = $oOrder->getFieldData('oxsenddate');
        } elseif ($this->deliveryDataWasJustRemoved()) {
            $orderStatus->status = OrderStatus::STATE_ERROR;
            $message->message = 'Provider shop removed the former order date';
        } elseif ($this->isJustPayed($oOrder)) {
            $orderStatus->status = OrderStatus::STATE_IN_PROCESS;
            $message->message = 'Provider shop has received payment on %payedDate';
            $message->values['payedDate'] = $oOrder->getFieldData('oxpaid');
        }
        $orderStatus->messages[] = $message;

        // update non open states only to bepado, to not report every initialized order again
        if ($orderStatus->status === OrderStatus::STATE_OPEN) {
            return;
        }

        try{
            $this->getSdk()->updateOrderStatus($orderStatus);
        } catch (\Exception $e){
            /** @var mf_sdk_logger_helper $helper */
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');
            $helper->writeBepadoLog($e->getMessage(), array('OrderState' => $orderStatus));
        }
    }

    /**
     * Create the SDK by the help of an helper.
     *
     * @return SDK
     */
    private function getSDK()
    {
        if (null === $this->sdk) {
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
            $this->sdk = $helper->instantiateSdk();
        }

        return $this->sdk;
    }

    /**
     * Orders can be switched from open to in_progress only to be marked as just payed.
     *
     * @param oxOrder $oOrder
     *
     * @return bool
     */
    private function isJustPayed(oxOrder $oOrder)
    {
        return $oOrder->getFieldData('oxpaid') !== '0000-00-00 00:00:00';
    }

    /**
     * Returns true when somebody hits the "set delivery" date button. Should work on both
     * order_main and order_overview as both should call oxOrder::save().
     *
     * @return bool
     */
    private function deliveryDataWasJustSet()
    {
        return $this->getVersionLayer()->getConfig()->getRequestParameter('fnc') == 'sendorder';
    }

    /**
     * Returns true when somebody hits the "reset delivery" date button. Should work on both
     * order_main and order_overview as both should call oxOrder::save().
     *
     * @return bool
     */
    private function deliveryDataWasJustRemoved()
    {
        return $this->getVersionLayer()->getConfig()->getRequestParameter('fnc') == 'resetorder';
    }
}
