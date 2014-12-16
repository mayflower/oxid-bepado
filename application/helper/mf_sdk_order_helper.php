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

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var array
     */
    protected $values;

    /**
     * @var OrderStatus
     */
    protected $orderStatus;


    public function updateOrderStatus($oOrder, $flag = null)
    {
       if ($this->areExportItems($oOrder)) {
            $this->updateStatus($oOrder, $flag);
       }
    }

    /**
     * @param oxorder $oOrder
     * @return bool
     */
    private function areExportItems($oOrder)
    {
        $oItems = $oOrder->getOrderArticles($oOrder);

        $helper = $this->getVersionLayer()->createNewObject('mf_sdk_article_helper');

        foreach ($oItems as $oItem) {
            $oArticle = $oItem->getArticle();
            $state = $helper->getArticleBepadoState($oArticle);

            if ($state != 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * OrderStatus Id = Order providerOrderId = oxorder Id
     *
     * @param oxorder $oOrder
     * @param int|null $flag
     */
    private function updateStatus($oOrder, $flag)
    {
        $this->orderStatus = new OrderStatus();
        $this->orderStatus->id = $oOrder->getId();
        $this->orderStatus->status = OrderStatus::STATE_OPEN;

        $this->message = new Message();
        $this->message->message = 'Provider shop has received order.';

        $this->values = array(
            'date'                        => date('o-m-d H:i:s'),
            OrderStatus::STATE_OPEN       => 1,
            'paydate'                     => 0,
            OrderStatus::STATE_IN_PROCESS => 0,
            'senddate'                    => 0,
            OrderStatus::STATE_DELIVERED  => 0,
            OrderStatus::STATE_CANCELED   => 0,
            OrderStatus::STATE_ERROR      => 0
        );

        $this->isOrderInProcess($oOrder);
        $this->isOrderDelivered($oOrder);
        $this->isOrderCanceled($oOrder);
        $this->isOrderError($oOrder, $flag);

        if ($flag && !($this->values[OrderStatus::STATE_ERROR])) {
            return;
        }

        $this->message->values = $this->values;

        $this->orderStatus->messages = $this->message;

        try{
            $this->getSdk()->updateOrderStatus($this->orderStatus);
        } catch (\Exception $e){
            /** @var mf_sdk_logger_helper $helper */
            $helper = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');
            $helper->writeBepadoLog($e->getMessage(), $this->values);
        }
    }

    private function isOrderInProcess($oOrder)
    {
        if ($oOrder->oxorder__oxpaid->rawValue !== '0000-00-00 00:00:00') {
            $this->message->message = 'Provider shop has received payment';
            $this->values['paydate']                     = $oOrder->oxorder__oxpaid->rawValue;
            $this->values[OrderStatus::STATE_IN_PROCESS] = 1;
            $this->values[OrderStatus::STATE_OPEN]       = 0;

            $this->orderStatus->status = OrderStatus::STATE_IN_PROCESS;
        }
    }

    private function isOrderDelivered($oOrder)
    {
        if ($this->getVersionLayer()->getConfig()->getRequestParameter('fnc') === 'sendorder') {
            $this->message->message = 'Provider shop has processed and delivered order.';
            $this->values[OrderStatus::STATE_DELIVERED]  = 1;
            $this->values['senddate']                    = $oOrder->oxorder__oxsenddate->value;
            $this->values[OrderStatus::STATE_OPEN]       = 0;
            $this->values[OrderStatus::STATE_IN_PROCESS] = 0;

            $this->orderStatus->status = OrderStatus::STATE_DELIVERED;
        }
    }

    private function isOrderCanceled($oOrder)
    {
        if ($oOrder->oxorder__oxstorno->rawValue) {
            $this->message->message = 'Provider shop has canceled order.';
            $this->values[OrderStatus::STATE_CANCELED]   = 1;
            $this->values[OrderStatus::STATE_OPEN]       = 0;
            $this->values[OrderStatus::STATE_IN_PROCESS] = 0;

            $this->orderStatus->status = OrderStatus::STATE_CANCELED;
        }
    }

    private function isOrderError($oOrder, $flag)
    {
        if (
            $oOrder->oxorder__oxtransstatus->rawValue === 'NOT_FINISHED' &&
            ($this->values[OrderStatus::STATE_DELIVERED] || $this->values[OrderStatus::STATE_IN_PROCESS])
        ) {
            $this->message->message = 'There was an error in the provider shop.';
            $this->values[OrderStatus::STATE_ERROR]      = 1;
            $this->values[OrderStatus::STATE_OPEN]       = 0;
            $this->values[OrderStatus::STATE_IN_PROCESS] = 0;

            $this->orderStatus->status = OrderStatus::STATE_ERROR;
        }

        if ($flag === 1 && ($this->values[OrderStatus::STATE_OPEN] || $this->values[OrderStatus::STATE_IN_PROCESS])) {
            $this->message->message = 'Provider shop has deleted order without processing it.';
            $this->values[OrderStatus::STATE_ERROR]      = 1;
            $this->values[OrderStatus::STATE_OPEN]       = 0;
            $this->values[OrderStatus::STATE_IN_PROCESS] = 0;

            $this->orderStatus->status = OrderStatus::STATE_ERROR;
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
}
