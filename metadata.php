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
    'url'     => 'http://www.mayflower.de/OXID-Bepado-Modul',
    'thumbnail' => 'thumbnail.jpg',
    'extend'  => array(
        'article_extend'                 => $aPaths['controllers'] . '/admin/mf_article_extend',
        'article_list'                   => $aPaths['controllers'] . '/admin/mf_article_list',
        'category_main'                  => $aPaths['controllers'] . '/admin/mf_category_main',
        'category_list'                  => $aPaths['controllers'] . '/admin/mf_category_list',
        'list_order'                     => $aPaths['controllers'] . '/admin/mf_list_order',
        'order_article'                  => $aPaths['controllers'] . '/admin/mf_order_article',
        'order_package'                  => $aPaths['controllers'] . '/admin/mf_order_package',

        'basket'                         => $aPaths['controllers'] . '/mf_basket',
        'order'                          => $aPaths['controllers'] . '/mf_order',

        'oxarticle'             => $aPaths['models'] . '/mf_bepado_oxarticle',
        'oxorder'               => $aPaths['models'] . '/mf_oxOrder',
        'oxorderarticle'        => $aPaths['models'] . '/mf_oxorderarticle',
    ),
    'files'   => array(
        'mfbepado'                       => $aPaths['controllers'] . '/mfBepado.php',
        'mf_configuration_module'        => $aPaths['controllers'] . '/admin/mf_configuration_module.php',
        'mf_configuration_module_main'   => $aPaths['controllers'] . '/admin/mf_configuration_module_main.php',
        'mf_configuration_module_list'   => $aPaths['controllers'] . '/admin/mf_configuration_module_list.php',
        'mf_configuration_category'      => $aPaths['controllers'] . '/admin/mf_configuration_category.php',
        'mf_configuration_category_main' => $aPaths['controllers'] . '/admin/mf_configuration_category_main.php',
        'mf_configuration_category_list' => $aPaths['controllers'] . '/admin/mf_configuration_category_list.php',
        'mf_configuration_unit'          => $aPaths['controllers'] . '/admin/mf_configuration_unit.php',
        'mf_configuration_unit_main'     => $aPaths['controllers'] . '/admin/mf_configuration_unit_main.php',
        'mf_configuration_unit_list'     => $aPaths['controllers'] . '/admin/mf_configuration_unit_list.php',
        'mf_product_import'              => $aPaths['controllers'] . '/admin/mf_product_import.php',
        'mf_product_import_main'         => $aPaths['controllers'] . '/admin/mf_product_import_main.php',
        'mf_product_import_list'         => $aPaths['controllers'] . '/admin/mf_product_import_list.php',
        'mf_product_export'              => $aPaths['controllers'] . '/admin/mf_product_export.php',
        'mf_product_export_main'         => $aPaths['controllers'] . '/admin/mf_product_export_main.php',
        'mf_product_export_list'         => $aPaths['controllers'] . '/admin/mf_product_export_list.php',

        'oxidproductfromshop'   => $aPaths['models'] . '/productFromShop.php',
        'oxidProductToShop'     => $aPaths['models'] . '/productToShop.php',
        'mfBepadoConfiguration'             => $aPaths['models'] . '/mfBepadoConfiguration.php',

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
        'mf_category_list.tpl'               => $aPaths['views'] . '/admin/tpl/mf_category_list.tpl',
        'mf_order_package.tpl'               => $aPaths['views'] . '/admin/tpl/mf_order_package.tpl',
        'mf_sdk_result.tpl'                  => $aPaths['views'] . '/tpl/mf_sdk_result.tpl',
        'mf_configuration_module.tpl'        => $aPaths['views'] . '/admin/tpl/mf_configuration_module.tpl',
        'mf_configuration_module_main.tpl'   => $aPaths['views'] . '/admin/tpl/mf_configuration_module_main.tpl',
        'mf_configuration_module_list.tpl'   => $aPaths['views'] . '/admin/tpl/mf_configuration_module_list.tpl',
        'mf_configuration_category.tpl'      => $aPaths['views'] . '/admin/tpl/mf_configuration_category.tpl',
        'mf_configuration_category_main.tpl' => $aPaths['views'] . '/admin/tpl/mf_configuration_category_main.tpl',
        'mf_configuration_category_list.tpl' => $aPaths['views'] . '/admin/tpl/mf_configuration_category_list.tpl',
        'mf_configuration_unit.tpl'          => $aPaths['views'] . '/admin/tpl/mf_configuration_unit.tpl',
        'mf_configuration_unit_main.tpl'     => $aPaths['views'] . '/admin/tpl/mf_configuration_unit_main.tpl',
        'mf_configuration_unit_list.tpl'     => $aPaths['views'] . '/admin/tpl/mf_configuration_unit_list.tpl',
        'mf_product_import.tpl'              => $aPaths['views'] . '/admin/tpl/mf_product_import.tpl',
        'mf_product_import_main.tpl'         => $aPaths['views'] . '/admin/tpl/mf_product_import_main.tpl',
        'mf_product_import_list.tpl'         => $aPaths['views'] . '/admin/tpl/mf_product_import_list.tpl',
        'mf_product_export.tpl'              => $aPaths['views'] . '/admin/tpl/mf_product_export.tpl',
        'mf_product_export_main.tpl'         => $aPaths['views'] . '/admin/tpl/mf_product_export_main.tpl',
        'mf_product_export_list.tpl'         => $aPaths['views'] . '/admin/tpl/mf_product_export_list.tpl',
    ),
    'events' => array(
        'onActivate'   => 'EventListener::onActivate',
    ),
);


