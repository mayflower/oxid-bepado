<?php

class mfBepado extends oxUbase {

    //protected $_sThisTemplate = 'ajax.tpl';

    public function render() {
        parent::render();
        // change this in SDK/DependencyResolver.php if this does not work
        putenv('_SOCIALNETWORK_HOST=sn.server1230-han.de-nserver.de');
        $this->instantiateSdk();
        return $this->_sThisTemplate;

    }

    protected function instantiateSdk() {
        // The page's url you created before
        $url = 'http://ps-dev-martin/index.php?cl=mfbepado';
        $pdoConnection = new PDO('mysql:dbname=shop;host=127.0.0.1','root', '');
        $from = oxnew('oxidproductfromshop');
        $to = oxnew('oxidproducttoshop');

        $builder = new \Bepado\SDK\SDKBuilder();
        $builder
            ->setApiKey('366dc0f6-a9ae-4a99-8d33-894ed2860511')
            ->setApiEndpointUrl($url)
            ->configurePDOGateway($pdoConnection)
            ->setProductToShop($to)
            ->setProductFromShop($from)
            ->setPluginSoftwareVersion('no one expects the spanish inquisition!')
        ;
        $sdk = $builder->build();

        echo $sdk->handle(file_get_contents('php://input'), $_SERVER);
    }
}
