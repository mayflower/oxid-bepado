<?php


$aMetadataVersion = "1.1";

$aPaths = array(
//    'components'  => $sModuleId . '/application/components',
    'controllers' => $sModuleId . '/application/controllers',
    'models'      => $sModuleId . '/application/models',
    'components'  => $sModuleId . '/application/components',
//    'core'        => $sModuleId . '/core',
//    'utils'       => $sModuleId . '/utils',
//    'admin'       => $sModuleId . '/application/controllers/admin',
);

$aModule = array(
    'id' => 'bepado',
    'title' => 'Bepado',
    'description' => array(
        'de' => 'Bepado Modul',
        'en' => 'Bepado module',
    ),
    'version' => '0.1',
    'author'  => 'Mayflower GmbH',
    'email'   => 'info@mayflower.de',
    'extend'  => array(),
    'files'       => array(
        'mfbepado'      => $aPaths['controllers'] . '/mfBepado.php',
        'mfcmp_bepado'  => $aPaths['components'] . '/mfcmp_bepado.php',
        'oxidProductFromShop' => $aPaths['models'] . '/productFromShop.php',
        'oxidProductToShop'   => $aPaths['models'] . '/productToShop.php',
    ),
    'templates' => array(),
);


spl_autoload_register(function ($class) {
    if (strpos($class, 'Bepado\\SDK') === 0) {
        $file = __DIR__ . '/Bepado/SDK/' . str_replace('\\', '/', $class) . '.php';
        require_once($file);
    }
});