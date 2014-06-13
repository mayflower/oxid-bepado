<?php

class mfBepado extends oxUbase {

    //protected $_sThisTemplate = 'ajax.tpl';

    public function render() {
        parent::render();
        echo "hallo welt 1";
        /*

        $foo = class_exists('Bepado\SDK\ProductFromShop');
        echo "klasse existiert: ".var_export($foo, true);

        $from = oxNew('oxidproductfromshop');
        echo "foobar";
        $to = oxNew('oxidProductToShop');
        echo var_export(array('ärräh', $from, $to), true);

        /* */
        return $this->_sThisTemplate;

    }

    public function doStuff() {
        echo "foo bar";
        return "";
    }
}
