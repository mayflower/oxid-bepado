<?php

/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

use Bepado\SDK\SDK;
use Bepado\SDK\Struct as Struct;

/**
 * This helper will serve function for the work the the SDK.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_helper extends mf_abstract_helper
{
    /**
     * Creates the bepado configuration depending on the current shop id.
     *
     * @return mfBepadoConfiguration
     */
    public function computeConfiguration()
    {
        /** @var mfBepadoConfiguration $oBepadoConfiguration */
        $oBepadoConfiguration = $this->getVersionLayer()->createNewObject('mfBepadoConfiguration');
        $oShopConfig = $this->getVersionLayer()->getConfig();

        $sShopId = $oShopConfig->getShopId();
        $oBepadoConfiguration->load($sShopId);

        if (!$oBepadoConfiguration->isLoaded()) {
            throw new \RuntimeException('No bebado configuration found for shop with id '.$sShopId);
        }

        $this->createApiEndPointUrl($oBepadoConfiguration);

        return $oBepadoConfiguration;
    }

    /**
     * Initializes the sdk with the current settings.
     *
     * API-Key and Endpoint are fetched from the settings and are
     * editable in the module settings.
     *
     * @param mfBepadoConfiguration $mfBepadoConfiguration
     *
     * @return SDK
     */
    public function instantiateSdk(mfBepadoConfiguration $mfBepadoConfiguration = null)
    {
        if (null === $mfBepadoConfiguration) {
            $mfBepadoConfiguration = $this->computeConfiguration();
        }
        $this->prepareHosts($mfBepadoConfiguration);

        if (null === $mfBepadoConfiguration->getApiEndpointUrl()) {
            $this->createApiEndPointUrl($mfBepadoConfiguration);
        }

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
            ->setApiKey($mfBepadoConfiguration->getApiKey())
            ->setApiEndpointUrl($mfBepadoConfiguration->getApiEndpointUrl())
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
     * @param mfBepadoConfiguration $sdkConfig
     */
    private function prepareHosts(mfBepadoConfiguration $sdkConfig)
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

        $sdkConfig = $this->computeConfiguration();
        $sdk = $this->instantiateSdk($sdkConfig);
        try {
            return $sdk->handle(file_get_contents('php://input'), $_SERVER);
        } catch (\Exception $e) {
            $logger->writeBepadoLog($e->getMessage());
        }
    }

    /**
     * The action that should happen on module activation.
     *
     * The sdk databases tables will be created by the sql queries delivered by the SDK
     *
     * This module adds some own database tables and extends some other.
     *
     * Base mapping for user/user group and payment methods will be created.
     *
     * @throws Exception
     */
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
            $sql = str_replace(array("\n","\r"), "", $sql);
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

        $this->createModuleConfigurationForShops();
        $this->createBaseUnitMapping();
    }

    /**
     * Each shop will get its own module configuration,
     * so we will loop through all of them. Existing ones, won't be overwritten.
     */
    private function createModuleConfigurationForShops()
    {
        /** @var oxConfig $oConfig */
        $oConfig = $this->getVersionLayer()->getConfig();

        foreach ($oConfig->getShopIds() as $iShopId) {
            /** @var mfBepadoConfiguration $oBepadoConfig */
            $oBepadoConfig = $this->getVersionLayer()->createNewObject('mfBepadoConfiguration');
            $oBepadoConfig->load($iShopId);
            if ($oBepadoConfig->isLoaded()) {
                // existing configuration won't be overwritten
                continue;
            }

            $oBepadoConfig->setId($iShopId);
            $oBepadoConfig
                ->setSandboxMode(true)
                ->setShopHintInBasket(false)
                ->setShopHintOnArticleDetails(false)
                ->setPurchaseGroup('A')
                ->setApiKey('some-key')
                ;
            $oBepadoConfig->save();
        }

    }

    /**
     * To have a better start after activating the shop, we will add some common
     * unit mappings.
     */
    private function createBaseUnitMapping()
    {
        $aUnits = oxRegistry::getLang()->getSimilarByKey("_UNIT_", null, false);

        foreach ($aUnits as $key => $unit) {
            /** @var mfBepadoUnit $oBepadoUnit */
            $oBepadoUnit = $this->getVersionLayer()->createNewObject('mfBepadoUnit');
            $oBepadoUnit->load($key);
            if ($oBepadoUnit->isLoaded()) {
                // won't insert twice after deactivation
                continue;
            }

            $oBepadoUnit->setId($key);
            $oBepadoUnit->setBepadoKey($oBepadoUnit->guessBepadoKey($key));
            $oBepadoUnit->save();
        }
    }

    /**
     * Method creates information that can be used as a marked place hint in different situations.
     *
     * @param mfBepadoConfiguration $bepadoConfiguration
     *
     * @param Struct\Product $product
     * @return array
     */
    public function computeMarketplaceHintForProduct(mfBepadoConfiguration $bepadoConfiguration, Struct\Product $product)
    {
        $sdk = $this->instantiateSdk($bepadoConfiguration);

        return $sdk->getShop($product->shopId);
    }

    /**
     * Will create the api endpoint url based on the current shop url.
     *
     * @param $mfBepadoConfiguration
     */
    public function createApiEndPointUrl($mfBepadoConfiguration)
    {
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $apiEndpointUrl = $oShopConfig->getShopUrl().mfBepadoConfiguration::API_ENDPOINT_URL_SUFFIX;
        $mfBepadoConfiguration->setApiEndpointUrl($apiEndpointUrl);
    }
}
