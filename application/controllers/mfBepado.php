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

        // load global oxid config
        $oShopConfig = oxRegistry::get('oxConfig');
        // module config
        $sLocalEndpoint = $oShopConfig->getConfigParam('sBepadoLocalEndpoint');
        $sApiKey = $oShopConfig->getConfigParam('sBepadoApiKey');
        // database config
        $sDbType = $oShopConfig->getConfigParam('dbType');
        $sDbHost = $oShopConfig->getConfigParam('dbHost');
        $sDbName = $oShopConfig->getConfigParam('dbName');
        $sDbUser = $oShopConfig->getConfigParam('dbUser');
        $sDbPwd = $oShopConfig->getConfigParam('dbPwd');

        $pdoConnection = new PDO($sDbType . ':dbname=' . $sDbName . ';host=' . $sDbHost,$sDbUser, $sDbPwd);
        $from = oxnew('oxidproductfromshop');
        $to = oxnew('oxidproducttoshop');

        $builder = new \Bepado\SDK\SDKBuilder();
        $builder
            ->setApiKey($sApiKey)
            ->setApiEndpointUrl($sLocalEndpoint)
            ->configurePDOGateway($pdoConnection)
            ->setProductToShop($to)
            ->setProductFromShop($from)
            ->setPluginSoftwareVersion('no one expects the spanish inquisition!')
        ;
        $sdk = $builder->build();

        echo $sdk->handle(file_get_contents('php://input'), $_SERVER);
    }
}
