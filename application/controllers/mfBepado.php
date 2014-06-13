<?php

class mfBepado extends oxUbase {

    //protected $_sThisTemplate = 'ajax.tpl';

    public function render() {
        parent::render();
        echo "hallo welt 1<br>\n";

        #$foo = class_exists('bepado\sdk\productfromshop');
        #echo "klasse existiert: ".var_export($foo, true);

        $from = oxnew('oxidproductfromshop');
        $to = oxnew('oxidproducttoshop');
        echo var_export(array('ärräh', $from, $to), true);

        /* */
        return $this->_sthistemplate;

    }

    public function dostuff() {
        echo "foo bar";
        return "";
    }
}
