<?php
use Bepado\SDK\ProductFromShop;
use Bepado\SDK\Struct\Order;
use Bepado\SDK\Struct\Product;

class oxidProductFromShop implements ProductFromShop
{
    protected function _convertToBepadoSdkProduct($id) {


        $sdkProduct = new Product();

        // load oxid article
        $oxProduct = oxNew('oxarticle');
        $oxProduct->load($id);

        // @TODO: check if article is marked for bepado

        $sdkProduct->vat = $oxProduct->getArticleVat();

        return $sdkProduct;


    }

    public function getProducts(array $ids)
    {

        $sdkProducts = array();

        foreach ($ids as $id) {
            $sdkProducts[] = $this->_convertToBepadoSdkProduct($id);
        }

        return $sdkProducts;


    }
    public function getExportedProductIDs()
    {
    }
    public function reserve(Order $order)
    {
    }
    public function buy(Order $order)
    {
    }
}

