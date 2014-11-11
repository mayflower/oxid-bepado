<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_helper
{
    /**
     * @return SDKConfig
     */
    public function createSdkConfigFromOxid()
    {
        /** @var SDKConfig $config */
        $config = oxRegistry::get('SDKConfig');
        // load global oxid config
        $oShopConfig = oxRegistry::get('oxConfig');
        // module config
        $sLocalEndpoint = $oShopConfig->getConfigParam('sBepadoLocalEndpoint');
        $sApiKey = $oShopConfig->getConfigParam('sBepadoApiKey');

        $config->setApiEndpointUrl($sLocalEndpoint);
        $config->setApiKey($sApiKey);

        return $config;
    }

    /**
     * Initializes the sdk with the current settings.
     *
     * API-Key and Endpoint are fetched from the settings and are
     * editable in the module settings.
     */
    public function instantiateSdk(SDKConfig $sdkConfig)
    {
        // @todo read host from config
        putenv('_SOCIALNETWORK_HOST=sn.server1230-han.de-nserver.de');
        // load global oxid config
        $oShopConfig = oxRegistry::get('oxConfig');

        // database config
        $sDbType = $oShopConfig->getConfigParam('dbType');
        $sDbHost = $oShopConfig->getConfigParam('dbHost');
        $sDbName = $oShopConfig->getConfigParam('dbName');
        $sDbUser = $oShopConfig->getConfigParam('dbUser');
        $sDbPwd = $oShopConfig->getConfigParam('dbPwd');

        $pdoConnection = new PDO($sDbType . ':dbname=' . $sDbName . ';host=' . $sDbHost,$sDbUser, $sDbPwd);
        $from = oxRegistry::get('oxidproductfromshop');
        $to = oxRegistry::get('oxidProductToShop');

        $builder = new \Bepado\SDK\SDKBuilder();
        $builder
            ->setApiKey($sdkConfig->getApiKey())
            ->setApiEndpointUrl($sdkConfig->getApiEndpointUrl())
            ->configurePDOGateway($pdoConnection)
            ->setProductToShop($to)
            ->setProductFromShop($from)
            ->setPluginSoftwareVersion('no one expects the spanish inquisition!')
        ;
        $sdk = $builder->build();

        return $sdk;
    }
}
 