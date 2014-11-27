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
            $sdkProduct = $product->getSdkProduct();

            $errorMsg = [];

            if ($product->isImportedFromBepado()) {
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
                   $errorMsg[] = 'This product is available only ' . $sdkProduct->availability . ' times.';
                }

                if ($errorMsg) {
                    $checkList = '<ul><li>' . implode('</li><li>', $errorMsg) . '</li></ul>';
                    $this->_aViewData['checkMsg'] = $checkList;
                }
            }
        }

        return $parent;
    }
} 