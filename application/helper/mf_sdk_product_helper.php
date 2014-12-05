<?php

use Bepado\SDK\Struct as Struct;

/**
 * Helper class for all (oxid) article (bepado) product communication and interactions.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_product_helper extends mf_abstract_helper
{
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

            if (!$oxBasketArticle->isImportedFromBepado()) {
                continue;
            }

            $product = $oxBasketArticle->getSdkProduct();
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
        $config = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($config);
        $results = [];

        try {
            $result = $sdk->checkProducts(array($sdkProduct));
            if (is_array($result)) {
                $results = array_merge($results, $result);
            }
        } catch (\Exception $e) {
            $results[] = new Struct\Message('Problem while checking the product', array());
        }

        return $results;
    }

    /**
     * Not done or functional afaik
     *
     * @param Order $sdkOrder
     *
     * @return bool[]
     */
    public function reserveProductWithBepado(Order $sdkOrder)
    {
        $config = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($config);

        $reservation = $sdk->reserveProducts($sdkOrder);
        if (!$reservation->success) {
            foreach ($reservation->messages as $shopId => $messages) {
                // handle individual error messages here
            }
        }

        $result = $sdk->checkout($reservation, $sdkOrder->localOrderId);

        return $result;
    }

    /**
     * @return mf_sdk_helper
     */
    private function getSdkHelper()
    {
        return $this->getVersionLayer()->createNewObject('mf_sdk_helper');
    }
}
