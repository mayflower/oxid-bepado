<?php


$aMetadataVersion = "1.1";

$aPaths = array(
//    'components'  => $sModuleId . '/application/components',
    'controllers' => $sModuleId . '/application/controllers',
    'models'      => $sModuleId . '/application/models',
    'components'  => $sModuleId . '/application/components',
    'core'        => $sModuleId . '/application/core',
//    'utils'       => $sModuleId . '/utils',
//    'admin'       => $sModuleId . '/application/controllers/admin',
    'views'       => $sModuleId . '/application/views',
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
    'extend'  => array(
        'module_config' => $aPaths['controllers'] . '/admin/mf_Module_Config'
    ),
    'files'       => array(
        'mfbepado'      => $aPaths['controllers'] . '/mfBepado.php',
        'mfcmp_bepado'  => $aPaths['components'] . '/mfcmp_bepado.php',

        'oxidproductfromshop' => $aPaths['models'] . '/productFromShop.php',
        'oxidProductToShop'   => $aPaths['models'] . '/productToShop.php',
        'SDKConfig'           => $aPaths['models'] . '/SDKConfig.php',

        'mf_sdk_helper' => $aPaths['core']. '/mf_sdk_helper.php',
    ),
    'templates' => array(
        'mf_module_config.tpl'     => $aPaths['views'] . '/admin/tpl/mf_module_config.tpl'
    ),
    'settings' => array(
        array('group' => 'main', 'name' => 'sBepadoLocalEndpoint', 'type' => 'str',  'value' => 'http://xxx.de/index.php?cl=mfbepado'),
        array('group' => 'main', 'name' => 'sBepadoApiKey',    'type' => 'str', 'value' => 'xxx'),
    )
);


