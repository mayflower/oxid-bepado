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

        'oxidproductfromshop' => $aPaths['models'] . '/productFromShop.php',
        'oxidProductToShop'   => $aPaths['models'] . '/productToShop.php',
    ),
    'templates' => array(),
    'settings' => array(
        array('group' => 'main', 'name' => 'sBepadoLocalEndpoint', 'type' => 'str',  'value' => 'http://xxx.de/index.php?cl=mfbepado'),
        array('group' => 'main', 'name' => 'sBepadoApiKey',    'type' => 'str', 'value' => 'xxx'),
    )
);


