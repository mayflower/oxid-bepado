<?php


class mf_basket extends mf_basket_parent
{
    /**
     * @var \Bepado\SDK\ProductToShop
     */
    private $productToShop;

    public function render()
    {
        $parent = parent::render();

        $aBasket = $this->_aViewData['oxcmp_basket']->getContents();

        /** @var  oxbasketitem $basketItem */
        foreach ($aBasket as $basketItem) {
            $amount = $basketItem->getAmount();

            /** @var mf_bepado_oxarticle $product */
            $product = $basketItem->getArticle();

            $errorMsg = [];

            if ($product->isImportedFromBepado()) {
                $sdkProduct = $product->getSdkProduct();
                $check = $product->checkProductWithBepardo($sdkProduct);
                // update sdkProduct
                foreach ($check as $shopId => $result) {
                    if ($result === true) {
                        // everything alright
                    } else {
                        $this->productToShop->insertOrUpdate($sdkProduct);
                        foreach ($result as $message) {
                            $errorMsg[] = $message;
                        }
                    }
                }
                $sdkProduct = $product->getSdkProduct();

                if ($amount > $sdkProduct->availability) {
                    $errorMsg[] =
                        'This product is available only ' .
                        $sdkProduct->availability . ' time' .
                        ($sdkProduct->availability == 1 ? '.' : 's.') .
                        ' Either delete the product or purchase the reduced amount.';
                    $basketItem->setAmount($sdkProduct->availability);
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

        return $parent;
    }
} 