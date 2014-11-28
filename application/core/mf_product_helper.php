<?php

use Bepado\SDK\Struct\Product;
use Bepado\SDK\Struct\Reservation;
use Bepado\SDK\Struct\Order;

class mf_product_helper
{
    /**
     * @var mf_sdk_helper
     */
    protected $_oSdkHelper;

    /**
     * @var \Bepado\SDK\ProductToShop
     */
    private $productToShop;

    /**
     * @param $oxBasket
     * @throws Exception
     * @throws oxArticleException
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     * @throws oxOutOfStockException
     */
    public function checkProductsWithBepado($oxBasket)
    {
        $aBasket = $oxBasket->getContents();

        /** @var  oxbasketitem $basketItem */
        foreach ($aBasket as $basketItem) {
            $amount = $basketItem->getAmount();

            /** @var mf_bepado_oxarticle $product */
            $product = $basketItem->getArticle();

            $errorMsg = [];

            if ($product->isImportedFromBepado()) {
                $sdkProduct = $product->getSdkProduct();
                $check = $this->checkProductWithBepardo($sdkProduct);
                foreach ($check as $shopId => $result) {
                    if ($result === true) {
                        // everything alright
                    } else {
                        // update sdkProduct
                        $this->productToShop->insertOrUpdate($sdkProduct);
                        foreach ($result as $message) {
                            $errorMsg[] = $message;
                        }
                    }
                }
                // get updated availability
                $availability = $product->getSdkProduct()->availability;

                if ($amount > $availability) {
                    if ($availability != 0) {
                        $errorMsg[] =
                            'This product is available only ' .
                            $sdkProduct->availability . ' time' .
                            ($sdkProduct->availability == 1 ? '.' : 's.') .
                            ' Either delete the product from your basket or purchase the reduced amount.';
                    } else {
                        $errorMsg[] =
                            'This product is not available at the moment.';
                    }
                    $basketItem->setAmount($availability);
                }

                if ($errorMsg) {
                    $checkList = '<ul><li><i>' . implode('</i></li><li><i>', $errorMsg) . '</i></li></ul>';
                    $basketItem->bepado_check = new oxField(
                        $checkList,
                        oxField::T_TEXT
                    );
                }
            }
        }

        $oxBasket->calculateBasket(true);
    }


    /**
     * @param Product $sdkProduct
     * @return array
     * @throws Exception
     */
    public function checkProductWithBepardo($sdkProduct)
    {
        $config = $this->getSdkHelper()->createSdkConfigFromOxid();
        $sdk = $this->getSdkHelper()->instantiateSdk($config);

        $results = [];
        #$results = [$sdkProduct->shopId => true];

        try {
            $results = $sdk->checkProducts(array($sdkProduct));

        } catch (\Exception $e) {
            # throw new Exception("No connection to SDK.");
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
    public function reserveProductWithBepado($sdkOrder)
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
        if ($this->_oModuleSdkHelper === null) {
            $this->_oModuleSdkHelper = oxNew('mf_sdk_helper');
        }

        return $this->_oModuleSdkHelper;
    }

} 