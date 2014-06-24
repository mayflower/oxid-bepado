<?php
use Bepado\SDK\ProductFromShop;
use Bepado\SDK\Struct\Order;
use Bepado\SDK\Struct\Product;

class oxidProductFromShop implements ProductFromShop
{
    protected function _convertToBepadoSdkProduct($id) {


        $sdkProduct = new Product();

        // load oxid article
        /**
         * @var oxarticle $oxProduct
         */
        $oxProduct = oxNew('oxarticle');
        $oxProduct->load($id);

        // @TODO: check if article is marked for bepado

        /*
                'shopId',
                'sourceId',
                'price',
                'purchasePrice',
                'currency',
                'availability',
                'relevance',
        */
        $sdkProduct->vat = $oxProduct->getArticleVat();

        $sdkProduct->sourceId = $id;
        $sdkProduct->title = $oxProduct->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $oxProduct->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription = $oxProduct->getLongDescription();
        $sdkProduct->vendor = $oxProduct->getVendor()->oxvendor__oxtitle->value;


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

