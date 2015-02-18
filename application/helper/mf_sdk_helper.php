<?php
use Bepado\SDK\SDK;
use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_helper extends mf_abstract_helper
{
    /**
     * @return SDKConfig
     */
    public function createSdkConfigFromOxid()
    {
        /** @var SDKConfig $config */
        $config = $this->getVersionLayer()->createNewObject('SDKConfig');
        // load global oxid config
        $oShopConfig = $this->getVersionLayer()->getConfig();
        // module config
        $sLocalEndpoint = $oShopConfig->getConfigParam('sBepadoLocalEndpoint');
        $sApiKey = $oShopConfig->getConfigParam('sBepadoApiKey');
        $sandboxMode = $oShopConfig->getConfigParam('sandboxMode');

        $config->setApiEndpointUrl($sLocalEndpoint);
        $config->setApiKey($sApiKey);
        $config->setSandboxMode($sandboxMode);

        if ($sandboxMode) {
            $config->setSocialnetworkHost(SDKConfig::SOCIALNETWORK_HOST_DEMO);
            $config->setTransactionHost(SDKConfig::TRANSACTION_HOST_DEMO);
            $config->setSearchHost(SDKConfig::SEARCH_HOST_DEMO);
        }

        return $config;
    }

    /**
     * Initializes the sdk with the current settings.
     *
     * API-Key and Endpoint are fetched from the settings and are
     * editable in the module settings.
     *
     * @param SDKConfig $sdkConfig
     *
     * @return SDK
     */
    public function instantiateSdk(SDKConfig $sdkConfig)
    {
        $this->prepareHosts($sdkConfig);

        // load global oxid config
        $oShopConfig = $this->getVersionLayer()->getConfig();

        // database config
        $sDbType = $oShopConfig->getConfigParam('dbType');
        $sDbHost = $oShopConfig->getConfigParam('dbHost');
        $sDbName = $oShopConfig->getConfigParam('dbName');
        $sDbUser = $oShopConfig->getConfigParam('dbUser');
        $sDbPwd  = $oShopConfig->getConfigParam('dbPwd');

        $pdoConnection = new PDO($sDbType . ':dbname=' . $sDbName . ';host=' . $sDbHost,$sDbUser, $sDbPwd);
        $pdoConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $from = $this->getVersionLayer()->createNewObject('oxidproductfromshop');
        $to = $this->getVersionLayer()->createNewObject('oxidproducttoshop');

        $builder = new \Bepado\SDK\SDKBuilder();
        $builder
            ->setApiKey($sdkConfig->getApiKey())
            ->setApiEndpointUrl($sdkConfig->getApiEndpointUrl())
            ->configurePDOGateway($pdoConnection)
            ->setProductToShop($to)
            ->setProductFromShop($from)
            ->setPluginSoftwareVersion('oxid v4.9ce/mf_bepado v1.0-RC')
        ;
        $sdk = $builder->build();

        return $sdk;
    }
    /**
     * Bepado send's urls to the images of external products. The oxid shop
     * need that image as local files in its own structure, so we need to
     * fetch and persist them.
     *
     * We will answer with an array like that array('oxid-field-name','path')
     * @param string $imagePath
     * @param int $key
     *
     * @throws Exception
     *
     * @return array
     */
    public function createOxidImageFromPath($imagePath, $key)
    {
        $oShopConfig = $this->getVersionLayer()->getConfig();

        $ch = curl_init($imagePath);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        $data = curl_exec($ch);
        curl_getinfo($ch);

        $maxFilesSize = ini_get('upload_max_filesize');
        $maxFilesSize = trim($maxFilesSize, 'M');
        $maxFilesSize = $maxFilesSize * 1024 * 1024;
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        if ($fileSize > $maxFilesSize) {
            throw new \Exception('File to large');
        }

        if (300 <= curl_getinfo($ch, CURLINFO_HTTP_CODE) || 0 === curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            throw new \Exception('Can not fetch the file in path ' . $imagePath);
        }

        curl_close($ch);

        $aImagePath = explode('/', $imagePath);
        $sImageName = $aImagePath[(count($aImagePath) - 1)];
        $destFileName = $oShopConfig->getMasterPictureDir() . 'product/' . ($key) . '/' . $sImageName;
        $fileHandle = fopen($destFileName, 'w');
        if (!$fileHandle) {
            throw new \Exception(sprintf('Can not create the file %s to write image data into.', $destFileName));
        }

        $writeResult = fwrite($fileHandle, $data, $fileSize);
        if (!$writeResult) {
            throw new \Exception('Problems while writing into file.');
        }

        fclose($fileHandle);

        return array('oxarticles__oxpic' . $key, $sImageName);
    }

    /**
     * Depending on the settings set the config the env var entries will be
     * set or not.
     *
     * @param SDKConfig $sdkConfig
     */
    private function prepareHosts(SDKConfig $sdkConfig)
    {
        if (null !== $sdkConfig->getSocialnetworkHost()) {
            putenv('_SOCIALNETWORK_HOST='.$sdkConfig->getSocialnetworkHost());
        }

        if (null !== $sdkConfig->getTransactionHost()) {
            putenv('_TRANSACTION_HOST='.$sdkConfig->getTransactionHost());
        }

        if (null !== $sdkConfig->getSearchHost()) {
            putenv('_SEARCH_HOST='.$sdkConfig->getSearchHost());
        }
    }

    /**
     * Wrapper around the controller handling for request on the sdk
     * functions.
     */
    public function handleRequest()
    {
        /** @var mf_sdk_logger_helper $logger */
        $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');

        $sdkConfig = $this->createSdkConfigFromOxid();
        $sdk = $this->instantiateSdk($sdkConfig);
        try {
            return $sdk->handle(file_get_contents('php://input'), $_SERVER);
        } catch (\Exception $e) {
            $logger->writeBepadoLog($e->getMessage());
        }
    }

    public function onModuleActivation()
    {
        /** @var mf_sdk_logger_helper $logger */
        $logger = $this->getVersionLayer()->createNewObject('mf_sdk_logger_helper');

        // add all SDK schema files to the list
        $schemaDir = __DIR__ . '/../../vendor/bepado/sdk/src/schema';
        $sqlFiles = array_filter(
            scandir($schemaDir),
            function ($file) { return substr($file, -4) === '.sql'; }
        );
        $paths = array();
        foreach ($sqlFiles as $file) {
            $paths[] = $schemaDir.'/'.$file;
        }

        // add all our files to the list
        $schemaDir = __DIR__ . '/../install';
        $sqlFiles = array_filter(
            scandir($schemaDir),
            function ($file) { return substr($file, -4) === '.sql'; }
        );
        foreach ($sqlFiles as $file) {
            $paths[] = $schemaDir.'/'.$file;
        }

        foreach ($paths as $sqlFile) {
            $sql = file_get_contents($sqlFile);
            $sql = str_replace("\n", "", $sql);
            $queries = explode(';', $sql);

            foreach ($queries as $query) {
                if (empty($query)) {
                    continue;
                }
                try {
                    $this->getVersionLayer()->getDb()->execute($query);
                } catch (\Exception $e) {
                    $logger->writeBepadoLog($e->getMessage());
                }

            }
        }

        /** @var oxGroups $oxUserGruop */
        $oxUserGruop = $this->getVersionLayer()->createNewObject('oxgroups');
        $oxUserGruop->load('bepadoshopgroup');
        if (!$oxUserGruop->isLoaded()) {
            $logger->writeBepadoLog('No bepado user group found.');
            throw new \Exception('No bepado user group found.');
        }

        /** @var oxDelivery $oxDelivery */
        $oxDelivery = $this->getVersionLayer()->createNewObject('oxdelivery');
        $oxDelivery->load('bepadoshippingrule');
        if (!$oxDelivery->isLoaded()) {
            $logger->writeBepadoLog('No bepado shipping found.');
            throw new \Exception('No bepado shipping found');
        }

        /** @var oxDeliveryset $oxDeliverySet */
        $oxDeliverySet = $this->getVersionLayer()->createNewObject('oxdeliveryset');
        $oxDeliverySet->load('bepadoshipping');
        if (!$oxDeliverySet->isLoaded()) {
            $logger->writeBepadoLog('No bepado shipping rule found.');
            throw new \Exception('No bepado shipping rule found');
        }

        $oObject2Delivery = $this->getVersionLayer()->createNewObject('oxbase');
        $oObject2Delivery->init('oxobject2delivery');
        $oObject2Delivery->oxobject2delivery__oxdeliveryid = new oxField('bepadoshipping');
        $oObject2Delivery->oxobject2delivery__oxobjectid = new oxField('bepadoshopgroup');
        $oObject2Delivery->oxobject2delivery__oxtype = new oxField("oxdelsetg");
        $oObject2Delivery->save();

        $oObject2Delivery = $this->getVersionLayer()->createNewObject('oxbase');
        $oObject2Delivery->init('oxobject2delivery');
        $oObject2Delivery->oxobject2delivery__oxdeliveryid = new oxField('bepadoshippingrule');
        $oObject2Delivery->oxobject2delivery__oxobjectid = new oxField('bepadoshipping');
        $oObject2Delivery->oxobject2delivery__oxtype = new oxField("oxdelset");
        $oObject2Delivery->save();
    }
}
