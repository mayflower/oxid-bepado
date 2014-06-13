<?php

class mfBepado extends oxUbase {

    //protected $_sThisTemplate = 'ajax.tpl';

    public function render() {
        parent::render();
        echo "hallo welt";

        /*
        $from = oxNew('oxidProductFromShop');

        $to = oxNew('oxidProductToShop');
        echo var_export(array($from, $to), true);
        */
        return $this->_sThisTemplate;

    }

    public function doStuff() {
        echo "foo bar";
        return "";
    }
}
