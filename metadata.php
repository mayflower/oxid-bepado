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
        'module_config'         => $aPaths['controllers'] . '/admin/mf_Module_Config',
        'article_extend'        => $aPaths['controllers'] . '/admin/mf_article_extend',
        'article_list'          => $aPaths['controllers'] . '/admin/mf_article_list',
        'category_main'         => $aPaths['controllers'] . '/admin/mf_category_main',
        'category_list'         => $aPaths['controllers'] . '/admin/mf_category_list',
        'basket'                => $aPaths['controllers'] . '/mf_basket',

        'oxarticle'             => $aPaths['models'] . '/mf_bepado_oxarticle',
    ),
    'files'   => array(
        'mfbepado'              => $aPaths['controllers'] . '/mfBepado.php',

        'mfcmp_bepado'          => $aPaths['components'] . '/mfcmp_bepado.php',

        'oxidproductfromshop'   => $aPaths['models'] . '/productFromShop.php',
        'oxidProductToShop'     => $aPaths['models'] . '/productToShop.php',
        'SDKConfig'             => $aPaths['models'] . '/SDKConfig.php',

        'mf_sdk_helper'         => $aPaths['core'] . '/mf_sdk_helper.php',
        'mf_sdk_converter'      => $aPaths['core'] . '/mf_sdk_converter.php',
        'EventListener'         => $aPaths['core'] . '/EventListener.php',
        'VersionLayerInterface' => $aPaths['core'] . '/interface/VersionLayerInterface.php',
        'VersionLayer460'       => $aPaths['core'] . '/VersionLayer460.php',
        'VersionLayer470'       => $aPaths['core'] . '/VersionLayer470.php',
        'VersionLayer490'       => $aPaths['core'] . '/VersionLayer490.php',
        'VersionLayer500'       => $aPaths['core'] . '/VersionLayer500.php',
        'VersionLayerFactory'   => $aPaths['core'] . '/VersionLayerFactory.php',
    ),
    'blocks' => array(
        array(
            'template' => 'article_extend.tpl',
            'block'    => 'admin_article_extend_media',
            'file'     => $aPaths['blocks'] . '/mf_article_extend.tpl'
        ),
        array(
            'template' => 'article_list.tpl',
            'block'    => 'admin_article_list_item',
            'file'     => $aPaths['blocks'] . '/mf_article_list.tpl'
        ),
        array(
            'template' => 'category_main.tpl',
            'block'    => 'admin_category_main_form',
            'file'     => $aPaths['blocks'] . '/mf_category_main.tpl'
        ),
        array(
            'template' => 'payment_main.tpl',
            'block'    => 'admin_payment_main_fields',
            'file'     => $aPaths['blocks'] . '/payment_main.tpl'
        ),
    ),
    'templates' => array(
        'mf_module_config.tpl' => $aPaths['views'] . '/admin/tpl/mf_module_config.tpl',
        'mf_category_list.tpl' => $aPaths['views'] . '/admin/tpl/mf_category_list.tpl',
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
        array(
            'group' => 'main',
            'name'  => 'prodMode',
            'type'  => 'bool',
            'value' => false,
        ),
        array(
            'group'       => 'main',
            'name'        => 'sPurchaseGroupChar',
            'type'        => 'select',
            'value'       => 'A',
            'constraints' => 'A|B|C',
            'position'    => 1
        ),
    ),
    'events' => array(
        'onActivate'   => 'EventListener::onActivate',
    ),
);


