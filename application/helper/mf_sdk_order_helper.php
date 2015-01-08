<?php

use Bepado\SDK\SDK;
use Bepado\SDK\Struct\Message;
use Bepado\SDK\Struct\OrderStatus;

class mf_sdk_order_helper extends mf_abstract_helper
{
    /**
     * @var SDK
     */
    protected $sdk;

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
        } elseif ($this->isJustPayed($oOrder)) {
            $orderStatus->status = OrderStatus::STATE_IN_PROCESS;
            $message->message = 'Provider shop has received payment on %payedDate';
            $message->values['payedDate'] = $oOrder->getFieldData('oxpaid');
        } elseif ($this->deliveryDataWasJustSet($oOrder)) {
            $orderStatus->status = OrderStatus::STATE_DELIVERED;
            $message->message = 'Provider shop has processed and delivered order on %senddate.';
            $message->values['senddate'] = $oOrder->getFieldData('oxsenddate');
        } elseif ($this->deliveryDataWasJustRemoved($oOrder)) {
            $orderStatus->status = OrderStatus::STATE_ERROR;
            $message->message = 'Provider shop removed the former order date';
        }
        $orderStatus->messages[] = $message;

        // update own state in DB when changed
        if ($orderStatus->status === $oOrder->getFieldData('mf_bepado_state')) {
            return;
        }

        $oOrder->mf_bepado_state = new oxField($orderStatus->status);
        $oOrder->save(false);

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
     * @return SDK
     */
    private function getSDK()
    {
        if (null === $this->sdk) {
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
            $config  = $helper->createSdkConfigFromOxid();
            $this->sdk = $helper->instantiateSdk($config);
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
        return $oOrder->getFieldData('oxpaid') !== '0000-00-00 00:00:00'
            && $oOrder->getFieldData('mf_bepado_state') === OrderStatus::STATE_OPEN;
    }

    /**
     * Returns true when somebody hits the "set delivery" date button. Should work on both
     * order_main and order_overview as both should call oxOrder::save().
     *
     * @param oxOrder $oxOrder
     *
     * @return bool
     */
    private function deliveryDataWasJustSet(oxOrder $oxOrder)
    {
        return $this->getVersionLayer()->getConfig()->getRequestParameter('fnc') === 'sendorder'
            && $oxOrder->getFieldData('mf_bepado_state') !== OrderStatus::STATE_DELIVERED;
    }

    /**
     * Returns true when somebody hits the "reset delivery" date button. Should work on both
     * order_main and order_overview as both should call oxOrder::save().
     *
     * @param oxOrder $oxOrder
     *
     * @return bool
     */
    private function deliveryDataWasJustRemoved(oxOrder $oxOrder)
    {
        return $this->getVersionLayer()->getConfig()->getRequestParameter('fnc') === 'resetorder'
        && $oxOrder->getFieldData('mf_bepado_state') === OrderStatus::STATE_DELIVERED;
    }
}
