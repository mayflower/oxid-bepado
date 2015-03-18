[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

[{if $readonly}]
[{assign var="readonly" value="readonly disabled"}]
[{else}]
[{assign var="readonly" value=""}]
[{/if}]


<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="mfunit_main">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>

<form name="myedit" id="myedit" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="cl" value="mfunit_main">
    <input type="hidden" name="fnc" value="">
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="editval[oxid]" value="[{ $oxid }]">

    <table cellspacing="0" cellpadding="0" border="0" width="98%">
        <tr>
            <td valign="top" class="edittext">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="MF_BEPADO_OXID_UNIT_KEY" }]
                        </td>
                        <td class="edittext" colspan="2">
                            <select name="editval[mfbepadounits__oxid]" class="editinput" style="max-width: 300px;"[{$readonly}]>
                                <option value="">[{ oxmultilang ident="MF_BEPADO_UNIT_NO_SELECT" }]</option>
                                [{foreach from=$oView->computeAvailableOxidUnits($edit) key=unitKey item=label}]
                                <option
                                        value="[{$unitKey}]"
                                        [{if $unitKey == $edit->mfbepadounits__oxid->value}]SELECTED[{/if}]>
                                    [{ $label }]
                                </option>
                                [{/foreach}]
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="MF_BEPADO_BEPADO_UNIT_KEY" }]
                        </td>
                        <td class="edittext" colspan="2">
                            <select name="editval[mfbepadounits__bepadounitkey]" class="editinput" style="max-width: 300px;"[{$readonly}]>
                                <option value="">[{ oxmultilang ident="MF_BEPADO_UNIT_NO_SELECT" }]</option>
                                [{foreach from=$oView->computeAvailableBepadoUnits($edit) key=unitKey item=label}]
                                <option
                                        value="[{$unitKey}]"
                                        [{if $unitKey == $edit->mfbepadounits__bepadounitkey->value}]SELECTED[{/if}]>
                                    [{ $label }]
                                </option>
                                [{/foreach}]
                            </select>
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
                        [{oxmultilang ident="MF_BEPADO_CONFIGURATION_UNIT_HINT_LEGEND"}]
                    </legend>
                    <br />
                    <table cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td class="edittext" width="120">
                                [{oxmultilang ident="MF_BEPADO_CONFIGURATION_UNIT_HINT"}]
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
