<?php

/**
 * Class to init and decide for the version layer.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class VersionLayerFactory
{
    /**
     * @var oxConfig
     */
    private static $_oConfig;

    /**
     * The current shop version.
     *
     * @var string
     */
    private $_sVersion;

    /**
     * @return VersionLayerInterface
     *
     * @throws Exception
     */
    public function create()
    {
        list($sLayerClassFile, $sLayerClass) = $this->_loadLayerConfig();

        if ($sLayerClassFile === null || $sLayerClass === null) {
            list($sLayerClassFile, $sLayerClass) = $this->_loadVersionLayer();
        }

        include_once __DIR__.'/' . $sLayerClassFile;

        return new $sLayerClass();
    }
    /**
     * Loads the version layer configuration from the database.
     *
     * @return array
     */
    private function _loadLayerConfig()
    {
        if ($this->_hasRegistry('getConfig')) {
            $oConfig = oxRegistry::getConfig();
        } else {
            $oConfig = $this->getConfig();
        }

        $sLayerClassFile = $oConfig->getShopConfVar('sLayerClassFile', null, 'bepado');
        $sLayerClass     = $oConfig->getShopConfVar('sLayerClass', null, 'bepado');

        return array($sLayerClassFile, $sLayerClass);
    }

    /**
     * Determines, whether we have the oxRegistry Class or not.
     *
     * @param string $sMethod Name of the method to check whether it exists or not.
     *
     * @return bool
     */
    private function _hasRegistry($sMethod = null)
    {
        $blRegistryExists = class_exists('oxRegistry');
        if (!$blRegistryExists || $sMethod === null) {
            return $blRegistryExists;
        }

        $blMethodExists = method_exists('oxRegistry', $sMethod);

        return $blRegistryExists && $blMethodExists;
    }

    /**
     * Loads a version layer. Throws an exception if there isn't a suitable layer.
     *
     * @throws Exception
     *
     * @return array
     */
    private function _loadVersionLayer()
    {
        /**
         * @var DirectoryIterator $oEntry
         */

        $sShopVersion         = $this->getShopVersion();
        $sMaxVersionLayerFile = 'VersionLayer' . str_replace('.', '', $sShopVersion) . '.php';
        $sLayerClassPattern   = 'VersionLayer'
            . str_replace('.', '', substr($sShopVersion, 0, strrpos($sShopVersion, '.')))
            . '*.php';
        $aLayerClasses        = array();
        $sCoreDir             = __DIR__;
        // TODO: inject iterator for testing
        $oDI                  = new DirectoryIterator($sCoreDir);

        foreach ($oDI as $oEntry) {
            if (
                $oEntry->isDir() ||
                $oEntry->isDot() ||
                !$oEntry->isReadable() ||
                !fnmatch($sLayerClassPattern, $oEntry->getFilename())
            ) {
                continue;
            }

            $aLayerClasses[] = $oEntry->getFilename();
        }

        natsort($aLayerClasses);

        do {
            $sLayerClassFile = array_pop($aLayerClasses);
            if ($sLayerClassFile === null) {
                throw new Exception("Can't find any suitable version layer class for your shop.");
            }
        } while (strnatcmp($sMaxVersionLayerFile, $sLayerClassFile) < 0);

        $sLayerClass = basename($sLayerClassFile, '.php');

        $this->_saveLayerConfig($sLayerClassFile, $sLayerClass);

        return array($sLayerClassFile, $sLayerClass);
    }

    /**
     * Saves the given version layer file- and class name to the shop config.
     *
     * @param $sLayerClassFile
     * @param $sLayerClass
     */
    private function _saveLayerConfig($sLayerClassFile, $sLayerClass)
    {
        if ($this->_hasRegistry('getConfig')) {
            $oConfig = oxRegistry::getConfig();
        } else {
            $oConfig = $this->getConfig();
        }

        $oConfig->saveShopConfVar('str', 'sLayerClassFile', $sLayerClassFile, null, 'bepado');
        $oConfig->saveShopConfVar('str', 'sLayerClass', $sLayerClass, null, 'bepado');
    }

    /**
     * oxConfig instance getter
     *
     * @return oxconfig
     */
    public function getConfig()
    {
        if (defined('OXID_PHP_UNIT')) {
            if (isset($this->unitCustModConf)) {
                return $this->unitCustModConf;
            }

            return oxRegistry::getConfig();
        }

        if (self::$_oConfig == null) {
            self::$_oConfig = oxRegistry::getConfig();
        }

        return self::$_oConfig;
    }

    /**
     * Returns shop version
     *
     * @return string
     */
    public function getShopVersion()
    {
        if ($this->_sVersion == null) {
            $this->_sVersion = $this->getConfig()->getActiveShop()->oxshops__oxversion->value;
        }

        return $this->_sVersion;
    }
}
 