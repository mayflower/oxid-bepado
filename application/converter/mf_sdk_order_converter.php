<?php

use Bepado\SDK\Struct as Struct;

/**
 * Converter class to transport the oxid order data into a sdk order object and back.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_order_converter extends mf_abstract_converter implements mf_converter_interface
{
    /**
     * @var mf_sdk_address_converter
     */
    protected $addressConverter;

    /**
     * @var mf_sdk_converter
     */
    protected $productConverter;

    public function __construct()
    {
        $this->addressConverter = $this->getVersionLayer()->createNewObject('mf_sdk_address_converter');
        $this->productConverter = $this->getVersionLayer()->createNewObject('mf_sdk_converter');
    }

    /**
     * {@inheritDoc}
     *
     * Creates a bepado order object from the information given by a oxOrder.
     *
     * @param oxOrder $object
     *
     * @return Struct\Order
     */
    public function fromShopToBepado($object, $parameters = array())
    {
        $sdkOrder = new Struct\Order();
        $sdkOrder->billingAddress = $this->addressConverter->fromShopToBepado($object, 'oxorder__oxbill');
        $sdkOrder->deliveryAddress = $this->addressConverter->fromShopToBepado($object, 'oxorder__oxdel');
        $sdkOrder->localOrderId = $object->getId();
        $sdkOrder->paymentType = $this->createSDKPaymentType($object);
        $sdkOrder->orderItems = $this->createOrderItems($object);

        foreach ($parameters as $property => $value) {
            $sdkOrder->$property = $value;
        }

        return $sdkOrder;
    }

    /**
     * {@inheritDoc}
     *
     * Things we won't ever convert here:
     *  - userId, cause this one will be created while checking out in the local shop
     *  - all delivery address stuff, cause it is created out of the user details
     *
     * @param Struct\Order $object
     *
     * @return oxOrder
     */
    public function fromBepadoToShop($object)
    {
        $oxOrder = oxNew('oxOrder');
        $parameters = array('oxorder__oxid' => null);
        $parameters = array_merge($parameters, $this->addressConverter->fromBepadoToShop($object->billingAddress, 'oxorder__oxbill'));
        $parameters['oxorder__oxpaymentid'] = $this->createOxidPaymentId($object);
        $oxOrder->assign($parameters);

        return $oxOrder;
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
        $oxPayment->load($object->oxorder__oxpaymentid->value);

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

        foreach ($orderArticles->getArray() as $orderArticle) {
            if (!$this->getVersionLayer()->createNewObject('mf_sdk_article_helper')->isOrderArticleImported($orderArticle)) {
                continue;
            }
            $article = $orderArticle->getArticle();
            $orderItem = new Struct\OrderItem();
            $orderItem->product = $article->getSdkProduct();
            $orderItem->count = $orderArticle->getFieldData('oxorderarticle__oxamount');
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
