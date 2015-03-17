[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
[{assign var="readonly" value="readonly disabled"}]
[{else}]
[{assign var="readonly" value=""}]
[{/if}]


<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="mf_configuration_module_main">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>

<form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="mf_configuration_module_main">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="editval[oxid]" value="[{ $oxid }]">

    <table cellspacing="0" cellpadding="0" border="0" width="98%">
        <tr>
            <td valign="top" class="edittext">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td class="edittext" width="120">
                            [{oxmultilang ident="MF_BEPADO_CONFIGURATION_MODULE_SANDBOXMODE"}]
                        </td>
                        <td class="edittext">
                            <input type="hidden" name="editval[mfbepadoconfiguration__sandboxmode]" value="0">
                            <input class="edittext" type="checkbox" name="editval[mfbepadoconfiguration__sandboxmode]" value='1' [{if $edit->mfbepadoconfiguration__sandboxmode->value == 1}]checked[{/if}] [{$readonly}]>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext" width="120">
                            [{oxmultilang ident="MF_BEPDO_SHOP_ID"}]
                        </td>
                        <td class="edittext">
                            <input class="edittext" type="text" name="editval[mfbepadoconfiguration__oxid]" value='[{$edit->mfbepadoconfiguration__oxid->value}]' readonly>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext" width="120">
                            [{oxmultilang ident="MF_BEPADO_CONFIGURATION_MODULE_APIKEY"}]
                        </td>
                        <td class="edittext">
                            <input class="edittext" type="text" name="editval[mfbepadoconfiguration__apikey]" value='[{$edit->mfbepadoconfiguration__apikey->value}]'>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="MF_BEPADO_PURCHASE_GROUP_CHAR" }]
                        </td>
                        <td class="edittext" colspan="2">
                            <select name="editval[mfbepadoconfiguration__purchasegroup]" class="editinput" style="max-width: 300px;"[{$readonly}]>
                                [{foreach from=$available_purchaseGroups item=char}]
                                <option
                                        value="[{$char}]"
                                        [{if $char == $edit->mfbepadoconfiguration__purchasegroup->value}]SELECTED[{/if}]>
                                    [{ oxmultilang ident="MF_BEPADO_PURCHASE_GROUP_CHAR_$char" }]
                                </option>
                                [{/foreach}]
                            </select>
                            [{ oxinputhelp ident="HELP_MF_BEPADO_PURCHASE_GROUP_CHAR" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext" colspan="2">
                            [{if isset($verified) && !$verified}]
                            [{ oxmultilang ident="api_key_not_verified" }]
                            [{/if}]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext" colspan="2"><br><br>
                            <input type="submit" class="edittext" name="save" value="[{oxmultilang ident="GENERAL_SAVE"}]" onClick="Javascript:document.myedit.fnc.value='save'" [{$readonly}]>
                        </td>
                    </tr>
                </table>
            </td>
            <!-- Anfang rechte Seite -->
            <td valign="top" class="edittext" align="left" width="50%">
                <fieldset title="[{oxmultilang ident="MF_BEPADO_CONFIGURATION_MODULE_SHOP_HINTS"}]" style="padding-left: 5px;">
                    <legend>
                        [{oxmultilang ident="MF_BEPADO_CONFIGURATION_MODULE_SHOP_HINTS"}]
                        [{ oxinputhelp ident="HELP_MF_BEPADO_CONFIGURATION_MODULE_SHOP_HINTS" }]
                    </legend>
                    <br />
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td class="edittext" width="120">
                                [{oxmultilang ident="MF_BEPADO_CONFIGURATION_MODULE_SHOP_HINT_BASKET"}]
                            </td>
                            <td class="edittext">
                                <input type="hidden" name="editval[mfbepadoconfiguration__marketplacehintbasket]" value="0">
                                <input class="edittext" type="checkbox" name="editval[mfbepadoconfiguration__marketplacehintbasket]" value='1' [{if $edit->mfbepadoconfiguration__marketplacehintbasket->value == 1}]checked[{/if}] [{$readonly}]>
                            </td>
                        </tr>
                        <tr>
                            <td class="edittext" width="120">
                                [{oxmultilang ident="MF_BEPADO_CONFIGURATION_MODULE_SHOP_HINT_ARTICLE_DETAILS"}]
                            </td>
                            <td class="edittext">
                                <input type="hidden" name="editval[mfbepadoconfiguration__marketplacehintarticle]" value="0">
                                <input class="edittext" type="checkbox" name="editval[mfbepadoconfiguration__marketplacehintarticle]" value='1' [{if $edit->mfbepadoconfiguration__marketplacehintarticle->value == 1}]checked[{/if}] [{$readonly}]>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </td>
        </tr>
    </table>
</form>

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
