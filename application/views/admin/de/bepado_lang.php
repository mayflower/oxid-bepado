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

// -------------------------------
// RESOURCE IDENTIFIER = STRING
// -------------------------------

use Bepado\SDK\Struct\Order;

$aLang = array(
    'charset'                          => 'UTF-8',
    'SHOP_MODULE_sBepadoLocalEndpoint' => 'Bepado apiEndpointUrl',
    'SHOP_MODULE_sBepadoApiKey'        => 'Bepado apiKey',
    'SHOP_MODULE_GROUP_main'           => 'Allgemeine Parameter',
    'SHOP_MODULE_sandboxMode'          => 'Modul im Sandbox-Modus',
    'SHOP_MODULE_sPurchaseGroupChar'   => 'Händlerpreis',
    'SHOP_MODULE_sPurchaseGroupChar_A' => 'Preis A',
    'SHOP_MODULE_sPurchaseGroupChar_B' => 'Preis B',
    'SHOP_MODULE_sPurchaseGroupChar_C' => 'Preis C',
    'api_key_not_verified'             => 'API-Key konnte nicht verifiziert werden und wurde nicht gespeichert.',
    'ARTICLE_MAIN_BEPADO'              => 'Artikel zum Export freigeben',
    'BEPADO_SETTINGS'                  => 'Einstellungen für Bepado',
    'HELP_ARTICLE_BEPADO_SETTINGS'     => 'Hier können Sie dieses Produkt zum Export nach Bepado freigeben.',

    'BEPADO_PAYMENT_TYPE'              => 'Bepado Zahlungsart',
    'HELP_BEPADO_PAYMENT_TYPE'         => 'Bitte wählen Sie eine Zahlungsart derer diese in Bepado entspricht',

    'BEPADO_PAYMENT_TYPE_'.Order::PAYMENT_ADVANCE      => 'Advanced',
    'BEPADO_PAYMENT_TYPE_'.Order::PAYMENT_INVOICE      => 'Rechnung',
    'BEPADO_PAYMENT_TYPE_'.Order::PAYMENT_DEBIT        => 'Lastschriftverfahren',
    'BEPADO_PAYMENT_TYPE_'.Order::PAYMENT_CREDITCARD   => 'Kreditkarte',
    'BEPADO_PAYMENT_TYPE_'.Order::PAYMENT_PROVIDER     => 'Provider',
    'BEPADO_PAYMENT_TYPE_'.Order::PAYMENT_OTHER        => 'Sonstiges',
    'BEPADO_PAYMENT_TYPE_'.Order::PAYMENT_UNKNOWN      => 'Unbekannt',

    'BEPADO_CATEGORY'                  => 'Bepado-Kategorie',
    'HELP_BEPADO_CATEGORY'             => 'Hier stellen Sie ein, welche Bepado-Kategorie Ihrer Shop-Kategorie entspricht.',
    'BEPADO_CATEGORY_SELECT'           => '-- keine --',

    'NAVIGATION_BEPADO'                        => 'Bepado Module',
    'mf_bepado_configuration'                  => 'Konfiguration',
    'mf_bepado_products'                        => 'Produkte verwalten',
    'mf_configuration_module'                  => 'Modul',
    'mf_configuration_module_main'             => 'Main',
    'mf_configuration_module_extend'           => 'Erweitert',
    'mf_configuration_category'                => 'Kategorien',
    'mf_configuration_category_main'           => 'Main',
    'mf_configuration_unit'                    => 'Maßeinheiten',
    'mf_configuration_unit_main'               => 'Main',
    'mf_product_export'                        => 'Export',
    'mf_product_export_main'                   => 'Main',
    'mf_product_import'                        => 'Import',
    'mf_product_import_main'                   => 'Main',
    'MF_CONFIGURATION'                         => 'Bepado Konfiguration',
    'MF_CONFIGURATION_MODULE_LIST_MENUSUBITEM' => 'Modul',
    'MF_CONFIGURATION_CATEGORY_LIST_MENUSUBITEM'=> 'Kategorie (Zuordnung)',
    'MF_CONFIGURATION_UNIT_LIST_MENUSUBITEM'   => 'Maßeinheit (Zuordnung)',
    'MF_PRODUCT_IMPORT_LIST_MENUSUBITEM'   => 'Import',
    'MF_PRODUCT_EXPORT_LIST_MENUSUBITEM'   => 'Export',
);
