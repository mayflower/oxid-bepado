<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class testController extends oxUbase
{
    public function render()
    {
        $order = new \Bepado\SDK\Struct\Order();
        $order->paymentType = 'debit';
        $order->shippingCosts = 100;
        $order->grossShippingCosts = 119;
        $order->providerShop = 'test_shop';

        $billAddress = new \Bepado\SDK\Struct\Address();
        $billAddress->state = 'bavaria';
        $billAddress->country = 'DEU';
        $billAddress->street = 'Gneisenaustr';
        $billAddress->additionalAddressLine = 'Hier bei max';
        $billAddress->city = 'Würzburg';
        $billAddress->streetNumber = '10/11';
        $billAddress->firstName = 'Maximilian';
        $billAddress->surName = 'Berghoff';
        $billAddress->company = 'Mayflower GmbH';
        $billAddress->email = 'Maximilian.Berghoff@mayflower.de';
        $billAddress->phone = '12345 00000';
        $billAddress->zip = '12345';
        $order->billingAddress = $billAddress;
        $order->deliveryAddress = $billAddress;

        $product = new \Bepado\SDK\Struct\Product();
        $product->sourceId = '05833e961f65616e55a2208c2ed7c6b8';
        $product->shopId = 'test_shop';
        $product->currency = 'EUR';
        $product->price = 99.99;
        $product->title = 'Great article';
        $product->shortDescription = 'This is a short Description';
        $product->url = "http://www.some-url.de";
        $product->vat = 0.19;
        $product->freeDelivery = true;

        $orderItem = new \Bepado\SDK\Struct\OrderItem();
        $orderItem->count = 10;
        $orderItem->product = $product;
        $order->orderItems[] = $orderItem;


        $product = new oxidProductFromShop();
        $orderId = $product->buy($order);
    }
}
 