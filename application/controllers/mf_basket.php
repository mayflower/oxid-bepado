<?php


class mf_basket extends mf_basket_parent
{
    public function render()
    {
        parent::render();

        $aBasket = $this->_aViewData['oxcmp_basket']->getContents();

        /** @var  oxbasketitem $basketItem */
        foreach ($aBasket as $basketItem) {
            $amount = $basketItem->getAmount();

            /** @var mf_bepado_oxarticle $product */
            $product = $basketItem->getArticle();

            if ($product->isImportedFromBepado()) {
                $check = $product->checkProductWithBepardo($product->getSdkProduct());
                // update sdkProduct
                $sdkProduct = $product->getSdkProduct();
                if ($amount > $sdkProduct->availability) {
                    // request amount bigger than availability
                }
            }
        }

        // render error messages into template an show updated products in basket
        // if order not possible, delete basket item

        return parent::render();
    }
} 