<?php
use Bepado\SDK\SDK;
use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_helper
{
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
        $config = $this->getVersionLayer()->createNewObject('SDKConfig');
        // load global oxid config
        $oShopConfig = $this->getVersionLayer()->getConfig();
        // module config
        $sLocalEndpoint = $oShopConfig->getConfigParam('sBepadoLocalEndpoint');
        $sApiKey = $oShopConfig->getConfigParam('sBepadoApiKey');
        $prodMode = $oShopConfig->getConfigParam('prodMode');

        $config->setApiEndpointUrl($sLocalEndpoint);
        $config->setApiKey($sApiKey);
        $config->setProdMode($prodMode);

        if (!$prodMode) {
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
        $from = $this->getVersionLayer()->createNewObject('oxidproductfromshop');
        $to = $this->getVersionLayer()->createNewObject('oxidproducttoshop');

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
            throw new \Exception('Can not create file to write image data into.');
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
            putenv('_TRANSACTION_HOST='.$sdkConfig->getSearchHost());
        }
    }

    /**
     * @param oxBasket $oxBasket
     */
    public function checkProductsInBasket(oxBasket $oxBasket)
    {
        /** @var  oxBasketItem[] $aBasket */
        $aBasket = $oxBasket->getContents();
        $countChanges = 0;

        foreach ($aBasket as $basketItem) {
            /** @var mf_bepado_oxarticle $oxBasketArticle */
            $oxBasketArticle = $basketItem->getArticle();
            $amount = $basketItem->getAmount();
            $errorMsg = [];
            $basketItem->bepado_check = new oxField('', oxField::T_TEXT);
            $changedAvailability = null;
            $changedPrice = null;

            if (!$oxBasketArticle->isImportedFromBepado()) {
                continue;
            }

            $product = $oxBasketArticle->getSdkProduct();
            foreach ($this->doCheckProduct($product) as $message) {
                if (isset($message->values['availability'])) {
                    $changedAvailability = $message->values['availability'];
                } elseif (isset($message->values['price'])) {
                    $changedPrice = $message->values['price'];
                }
            }

            if (null !== $changedAvailability && $amount > $changedAvailability) {
                if ($changedAvailability != 0) {
                    $errorMsg[] = 'This product is available only '.$changedAvailability.' time'
                        .($changedAvailability == 1 ? '.' : 's.').' Either delete the
                        product from your basket or purchase the reduced amount.';
                } else {
                    $errorMsg[] = 'This product is not available at the moment.';
                }
                $basketItem->setAmount($changedAvailability);
            }

            if (null !== $changedPrice) {
                $basketItem->setPrice(new oxPrice($changedPrice));
                $errorMsg[] = 'The price has changed.';
            }

            if ($errorMsg) {
                $countChanges++;
                $basketItem->bepado_check = new oxField(
                    '<ul><li><i>' . implode('</i></li><li><i>', $errorMsg) . '</i></li></ul>',
                    oxField::T_TEXT
                );
            }

        }

        // do calculate when there where changes only
        if ($countChanges > 0) {
            $oxBasket->calculateBasket(true);
        }
    }

    /**
     * @param Struct\Product $sdkProduct
     *
     * @return Struct\Message[]
     */
    private function doCheckProduct($sdkProduct)
    {
        $config = $this->createSdkConfigFromOxid();
        $sdk = $this->instantiateSdk($config);
        $results = [];

        try {
            $results = $sdk->checkProducts(array($sdkProduct));
        } catch (\Exception $e) {
            $results[] = new Struct\Message(
                'Problem while checking the product',
                array()
            );
        }

        return $results;
    }

    /**
     * Not done or functional afaik
     *
     * @param Order $sdkOrder
     *
     * @return bool[]
     */
    public function reserveProductWithBepado(Order $sdkOrder)
    {
        $config = $this->createSdkConfigFromOxid();
        $sdk = $this->instantiateSdk($config);

        $reservation = $sdk->reserveProducts($sdkOrder);
        if (!$reservation->success) {
            foreach ($reservation->messages as $shopId => $messages) {
                // handle individual error messages here
            }
        }

        $result = $sdk->checkout($reservation, $sdkOrder->localOrderId);

        return $result;
    }
}
 