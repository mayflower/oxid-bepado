<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_helper
{
    /**
     * Host to talk with when testing bepado.
     */
    const BEPADO_HOST_DEMO = 'sn.server1230-han.de-nserver.de';

    /**
     * Host to talk with when going live with bepado.
     *
     * @todo insert the right url
     */
    const BEPADO_HOST_LIVE = 'LIVE:sn.server1230-han.de-nserver.de';

    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    /**
     * @return SDKConfig
     */
    public function createSdkConfigFromOxid()
    {
        /** @var SDKConfig $config */
        $config = oxNew('SDKConfig');
        // load global oxid config
        $oShopConfig = $this->getVersionLayer()->getConfig();
        // module config
        $sLocalEndpoint = $oShopConfig->getConfigParam('sBepadoLocalEndpoint');
        $sApiKey = $oShopConfig->getConfigParam('sBepadoApiKey');
        $prodMode = $oShopConfig->getConfigParam('prodMode');

        $config->setApiEndpointUrl($sLocalEndpoint);
        $config->setApiKey($sApiKey);
        $config->setProdMode($prodMode);

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
        $host = $sdkConfig->getProdMode() ? self::BEPADO_HOST_LIVE : self::BEPADO_HOST_DEMO;
        putenv('_SOCIALNETWORK_HOST='.$host);

        // load global oxid config
        $oShopConfig = $this->getVersionLayer()->getConfig();

        // database config
        $sDbType = $oShopConfig->getConfigParam('dbType');
        $sDbHost = $oShopConfig->getConfigParam('dbHost');
        $sDbName = $oShopConfig->getConfigParam('dbName');
        $sDbUser = $oShopConfig->getConfigParam('dbUser');
        $sDbPwd  = $oShopConfig->getConfigParam('dbPwd');

        $pdoConnection = new PDO($sDbType . ':dbname=' . $sDbName . ';host=' . $sDbHost,$sDbUser, $sDbPwd);
        $from = oxnew('oxidproductfromshop');
        $to = oxnew('oxidproducttoshop');

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

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    private function getVersionLayer()
    {
        if (null == $this->_oVersionLayer) {
            /** @var VersionLayerFactory $factory */
            $factory = oxNew('VersionLayerFactory');
            $this->_oVersionLayer = $factory->create();
        }

        return $this->_oVersionLayer;
    }

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        $data = curl_exec($ch);
        $info = curl_getinfo($ch);

        $maxFilesSize = ini_get('upload_max_filesize');
        $maxFilesSize = trim($maxFilesSize, 'M');
        $maxFilesSize = $maxFilesSize*1024*1024;
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        if ($fileSize > $maxFilesSize) {
            throw new \Exception('File to large');
        }

        if (300 <= curl_getinfo($ch, CURLINFO_HTTP_CODE)  || 0 === curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
            throw new \Exception('Can not fetch the file in path '.$imagePath);
        }

        curl_close($ch);

        $aImagePath = explode('/', $imagePath);
        $sImageName = $aImagePath[(count($aImagePath) - 1)];
        $destFileName = $oShopConfig->getMasterPictureDir().'product/'.($key).'/'.$sImageName;
        $fileHandle = fopen($destFileName, 'w');
        if (!$fileHandle) {
            throw new \Exception('Can not create file to write image data into.');
        }

        $writeResult = fwrite($fileHandle,$data);
        if (!$writeResult) {
            throw new \Exception('Problems while writing into file.');
        }

        fclose($fileHandle);
        return array('oxarticles__oxpic'.$key, $sImageName);
    }
}
 