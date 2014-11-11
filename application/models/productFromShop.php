<?php

use Bepado\SDK\ProductFromShop;
use Bepado\SDK\Struct\Order;
use Bepado\SDK\Struct\Product;

class oxidProductFromShop implements ProductFromShop
{
    public function getProducts(array $ids)
    {
        $sdkProducts = array();

        foreach ($ids as $id) {
            $sdkProducts[] = $this->_convertToBepadoSdkProduct($id);
        }

        return $sdkProducts;
    }

    protected function _convertToBepadoSdkProduct($id)
    {
        $sdkProduct = new Product();

        // load oxid article
        /**
         * @var oxarticle $oxProduct
         */
        $oxProduct = oxNew('oxarticle');
        $oxProduct->load($id);

        // @TODO: check if article is marked for bepado

        $sdkProduct->vat = $oxProduct->getArticleVat();

        $sdkProduct->sourceId = $id;
        $sdkProduct->title = $oxProduct->oxarticles__oxtitle->value;
        $sdkProduct->shortDescription = $oxProduct->oxarticles__oxshortdesc->value;
        $sdkProduct->longDescription = $oxProduct->getLongDescription();
        $sdkProduct->vendor = $oxProduct->getVendor()->oxvendor__oxtitle->value;

        //$sdkProduct->price = 1234;// Preis für Endkunden
        //$sdkProduct->purchasePrice = 1234;// Nettoeinkaufspreis für Händler
        //$sdkProduct->currency = 'EUR';
        //$sdkProduct->availability = 0;

        $sdkProduct->images = $this->mapImages($oxProduct);
        $sdkProduct->categories = $this->mapCategories($oxProduct);
        $sdkProduct->attributes = $this->mapAttributes($oxProduct);


        return $sdkProduct;
    }

    private function mapImages($oxProduct)
    {
        return array(); // links zu bildern. 1. bild ist hauptbild
    }

    private function mapCategories($oxProduct)
    {
        // Oxid Kategorien auf bepado/Google Shopping mappen
        // Zwei Möglichkeiten:
        // - im einfachsten Fall an in einer Extra Tabelle zu einem Produkt die Google Kategorie zu konfigurieren.
        // - Endbenutzer generisches Mapping seiner Kategorien auf Google Kategorien erlauben
        //
        // Die Kategorien können in der UI über $sdk->getCategories(); abgefragt werden.
        //
        return array();
    }

    private function mapAttributes($oxProduct)
    {
        // Am Anfang nicht zwingend notwendig, aber hier werden die ATTRIBUTE_ Konstanten
        // auf Bepado\SDK\Struct\Product als Keys gesetzt, die für
        // Grundpreisbrechnung notwendig sind.
        return array();
    }

    public function getExportedProductIDs()
    {
        throw new \BadMethodCallException('Not needed in oxid module.');
    }

    public function reserve(Order $order)
    {
        // not using explicit reservation handling.
    }

    public function buy(Order $order)
    {
        // Hier muss die Bepado Order in eine Oxid Bestellung umgewandelt
        // werden. Rückgabewert ist die ID der Bestellung
        //
        $oxOrder = oxNew('oxorder'); // ??

        return $oxOrder->getOxID();
    }
}

