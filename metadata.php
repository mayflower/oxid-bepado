<?php


$aMetadataVersion = "1.1";

$aPaths = array(
    'components'  => $sModuleId . '/application/components',
    'controllers' => $sModuleId . '/application/controllers',
    'models'      => $sModuleId . '/application/models',
    'core'        => $sModuleId . '/application/core',
//    'utils'       => $sModuleId . '/utils',
//    'admin'       => $sModuleId . '/application/controllers/admin',
    'views'       => $sModuleId . '/application/views',
    'blocks'      => 'application/views/blocks',
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
        'module_config'  => $aPaths['controllers'] . '/admin/mf_Module_Config',
        'oxarticle'      => $aPaths['models'] . '/mf_bepado_oxarticle'
    ),
    'files'       => array(
        'mfbepado'       => $aPaths['controllers'] . '/mfBepado.php',
        'testController' => $aPaths['controllers'] . '/testController.php',

        'mfcmp_bepado'  => $aPaths['components'] . '/mfcmp_bepado.php',

        'oxidproductfromshop' => $aPaths['models'] . '/productFromShop.php',
        'oxidProductToShop'   => $aPaths['models'] . '/productToShop.php',
        'SDKConfig'           => $aPaths['models'] . '/SDKConfig.php',

        'mf_sdk_helper'    => $aPaths['core'] . '/mf_sdk_helper.php',
        'mf_sdk_converter' => $aPaths['core'] . '/mf_sdk_converter.php',
        'EventListener'    => $aPaths['core'] .'/EventListener.php'
    ),
    'templates' => array(
        'mf_module_config.tpl' => $aPaths['views'] . '/admin/tpl/mf_module_config.tpl',
    ),
    'settings' => array(
        array(
            'group' => 'main',
            'name'  => 'sBepadoLocalEndpoint',
            'type'  => 'str',
            'value' => 'http://xxx.de/index.php?cl=mfbepado'
        ),
        array(
            'group' => 'main',
            'name'  => 'sBepadoApiKey',
            'type'  => 'str',
            'value' => 'xxx'
        ),
    ),
    'events' => array(
        'onActivate'   => 'EventListener::onActivate',
    ),
    'blocks' => array(
        array(
            'template' => 'article_extend.tpl',
            'block'    => 'admin_article_extend_media',
            'file'     => 'application/views/blocks/article_extend.tpl'
        ),
        array(
            'template' => 'article_list.tpl',
            'block'    => 'admin_article_list_item',
            'file'     => $aPaths['blocks'] . '/article_list.tpl'
        ),
        array(
            'template' => 'payment_main.tpl',
            'block'    => 'admin_payment_main_fields',
            'file'     => 'application/views/blocks/payment_main.tpl'
        ),
    )
);


