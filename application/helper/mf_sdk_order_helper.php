<?php

use Bepado\SDK\SDK;
use Bepado\SDK\Struct\OrderStatus;

class mf_sdk_order_helper extends mf_abstract_helper
{
    /**
     * @var SDK
     */
    protected $sdk;

    /**
     * @var OrderStatus
     */
    protected $orderStatus;

    /**
     * @param oxorder $order
     * @param int|null $flag
     */
    public function setSdkOrderStatus($order, $flag = null)
    {
        //OrderStatus Id = Order providerOrderId = oxorder Id
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
