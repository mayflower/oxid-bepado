<?php


$aMetadataVersion = "1.1";

$aPaths = array(
    'components'  => $sModuleId . '/application/components',
    'controllers' => $sModuleId . '/application/controllers',
    'models'      => $sModuleId . '/application/models',
    'core'        => $sModuleId . '/application/core',
    'helper'      => $sModuleId . '/application/helper',
    'converter'   => $sModuleId . '/application/converter',
//    'admin'       => $sModuleId . '/application/controllers/admin',
    'views'       => $sModuleId . '/application/views',
    'blocks'      => 'application/views/blocks',
);

$aModule = array(
    'id' => 'bepado',
    'title' => 'Bepado-Plugin',
    'description' => array(
        'de' => 'Verbinden Sie ihren Oxid eShop mit dem Bepado Netzwerk',
        'en' => 'To connect your Oxid eShop with the Bepado network',
    ),
    'version' => '1.0.0',
    'author'  => 'Mayflower GmbH',
    'email'   => 'kontakt@mayflower.de',
    'url'     => 'www.mayflower.de/OXID-Bepado-Modul-von_mayflower',
    'thumbnail' => 'thumbnail.jpg',
    'extend'  => array(
        'module_config'         => $aPaths['controllers'] . '/admin/mf_Module_Config',
        'article_extend'        => $aPaths['controllers'] . '/admin/mf_article_extend',
        'article_list'          => $aPaths['controllers'] . '/admin/mf_article_list',
        'category_main'         => $aPaths['controllers'] . '/admin/mf_category_main',
        'category_list'         => $aPaths['controllers'] . '/admin/mf_category_list',
        'list_order'            => $aPaths['controllers'] . '/admin/mf_list_order',
        'order_article'         => $aPaths['controllers'] . '/admin/mf_order_article',
        'order_package'         => $aPaths['controllers'] . '/admin/mf_order_package',
        'basket'                => $aPaths['controllers'] . '/mf_basket',
        'order'                 => $aPaths['controllers'] . '/mf_order',

        'oxarticle'             => $aPaths['models'] . '/mf_bepado_oxarticle',
        'oxorder'               => $aPaths['models'] . '/mf_oxOrder',
        'oxorderarticle'        => $aPaths['models'] . '/mf_oxorderarticle',
    ),
    'files'   => array(
        'mfbepado'              => $aPaths['controllers'] . '/mfBepado.php',

        'oxidproductfromshop'   => $aPaths['models'] . '/productFromShop.php',
        'oxidProductToShop'     => $aPaths['models'] . '/productToShop.php',
        'SDKConfig'             => $aPaths['models'] . '/SDKConfig.php',

        'mf_converter_interface'   => $aPaths['converter'] . '/mf_converter_interface.php',
        'mf_abstract_converter'    => $aPaths['converter'] . '/mf_abstract_converter.php',
        'mf_sdk_converter'         => $aPaths['converter'] . '/mf_sdk_converter.php',
        'mf_sdk_order_converter'   => $aPaths['converter'] . '/mf_sdk_order_converter.php',
        'mf_sdk_address_converter' => $aPaths['converter'] . '/mf_sdk_address_converter.php',

        'EventListener'         => $aPaths['core'] . '/EventListener.php',
        'VersionLayerInterface' => $aPaths['core'] . '/interface/VersionLayerInterface.php',
        'VersionLayer460'       => $aPaths['core'] . '/VersionLayer460.php',
        'VersionLayer470'       => $aPaths['core'] . '/VersionLayer470.php',
        'VersionLayer490'       => $aPaths['core'] . '/VersionLayer490.php',
        'VersionLayer500'       => $aPaths['core'] . '/VersionLayer500.php',
        'VersionLayerFactory'   => $aPaths['core'] . '/VersionLayerFactory.php',

        'mf_abstract_helper'          => $aPaths['helper'] . '/mf_abstract_helper.php',
        'mf_sdk_helper'               => $aPaths['helper'] . '/mf_sdk_helper.php',
        'mf_sdk_product_helper'       => $aPaths['helper'] . '/mf_sdk_product_helper.php',
        'mf_sdk_logger_helper'        => $aPaths['helper'] . '/mf_sdk_logger_helper.php',
        'mf_sdk_article_helper'       => $aPaths['helper'] . '/mf_sdk_article_helper.php',
        'mf_sdk_order_helper'         => $aPaths['helper'] . '/mf_sdk_order_helper.php',
        'mf_module_helper'            => $aPaths['helper'] . '/mf_module_helper.php',
        'mf_article_number_generator' => $aPaths['helper'] . '/mf_article_number_generator.php',
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
        array(
            'template' => 'list_order.tpl',
            'block'    => 'admin_list_order_item',
            'file'     => $aPaths['blocks'] . '/mf_list_order.tpl'
        ),
        array(
            'template' => 'order_article.tpl',
            'block'    => 'admin_order_article_listitem',
            'file'     => $aPaths['blocks'] . '/mf_order_article.tpl'
        ),
        array(
            'template' => 'page/checkout/payment.tpl',
            'block'    => 'checkout_payment_errors',
            'file'     => $aPaths['blocks'] . '/mf_checkout_payment_errors.tpl'
        ),
    ),
    'templates' => array(
        'mf_module_config.tpl'     => $aPaths['views'] . '/admin/tpl/mf_module_config.tpl',
        'mf_category_list.tpl'     => $aPaths['views'] . '/admin/tpl/mf_category_list.tpl',
        'mf_order_package.tpl'     => $aPaths['views'] . '/admin/tpl/mf_order_package.tpl',
        'mf_sdk_result.tpl' => $aPaths['views'] . '/tpl/mf_sdk_result.tpl',
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
            'name'  => 'sandboxMode',
            'type'  => 'bool',
            'value' => true,
        ),
        array(
            'group'       => 'main',
            'name'        => 'sPurchaseGroupChar',
            'type'        => 'select',
            'value'       => 'B',
            'constraints' => 'A|B|C',
            'position'    => 1
        ),
    ),
    'events' => array(
        'onActivate'   => 'EventListener::onActivate',
    ),
);


