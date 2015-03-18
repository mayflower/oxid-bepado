[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign}]

<script type="text/javascript">
    function editThis( sID )
    {
        var oTransfer = top.basefrm.edit.document.getElementById( "transfer" );
        oTransfer.oxid.value = sID;
        oTransfer.cl.value = top.basefrm.list.sDefClass;

        //forcing edit frame to reload after submit
        top.forceReloadingEditFrame();

        var oSearch = top.basefrm.list.document.getElementById( "search" );
        oSearch.oxid.value = sID;
        oSearch.actedit.value = 0;
        oSearch.submit();
    }
    window.onload = function ()
    {
        [{ if $updatelist == 1}]
        top.oxid.admin.updateList('[{ $oxid }]');
        [{ /if}]
        var oField = top.oxid.admin.getLockTarget();
        oField.onchange = oField.onkeyup = oField.onmouseout = top.oxid.admin.unlockSave;
    }
</script>


[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]


<form name="transfer" id="transfer" action="[{ $oViewConf->getSelfLink() }]" method="post">
    [{ $oViewConf->getHiddenSid() }]
    <input type="hidden" name="oxid" value="[{ $oxid }]">
    <input type="hidden" name="cl" value="mf_product_export_main">
    <input type="hidden" name="editlanguage" value="[{ $editlanguage }]">
</form>

<table cellspacing="0" cellpadding="0" border="0" style="width:98%;">
    <form
            name="myedit"
            id="myedit"
            action="[{ $oViewConf->getSelfLink() }]"
            method="post"
            onSubmit="return copyLongDesc( 'oxarticles__oxlongdesc' );"
            style="padding: 0px;margin: 0px;height:0px;" style="padding: 0px;margin: 0px;height:0px;">
        [{$oViewConf->getHiddenSid()}]
        <input type="hidden" name="cl" value="mf_product_export_main">
        <input type="hidden" name="fnc" value="">
        <input type="hidden" name="oxid" value="[{ $oxid }]">
        <input type="hidden" name="editval[oxarticles__oxid]" value="[{ $oxid }]">
        <input type="hidden" name="editval[oxarticles__oxlongdesc]" value="">

        <tr height="10"><td></td><td></td></tr>

        [{if $oxid != -1}]
        [{assign var="oArticle" value=$edit->oxArticle}]
        [{assign var="oProduct" value=$edit->mfBepadoProduct}]
        <tr>
            <td width="15"></td>
            <td valign="top" class="edittext">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td colspan="2">
                            [{if $errorsavingatricle eq 1}]
                            <div class="errorbox">[{ oxmultilang ident=$errorMessage }]</div>
                            [{/if}]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext" width="120">
                            [{ oxmultilang ident="ARTICLE_MAIN_ACTIVE" }]
                        </td>
                        <td class="edittext">
                            <input type="hidden" name="editval[oxArticle][oxarticles__oxactive]" value="0">
                            <input
                                    class="edittext"
                                    type="checkbox"
                                    name="editval[oxArticle][oxarticles__oxactive]"
                                    value='1'
                                    [{if $oArticle->oxarticles__oxactive->value == 1}]checked[{/if}]
                                    [{ $readonly }]>
                            [{ oxinputhelp ident="HELP_ARTICLE_MAIN_ACTIVE" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="MF_PRODUCT_EXPORT_TITLE" }]&nbsp;
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="32"
                                    maxlength="[{$oArticle->oxarticles__oxtitle->fldmax_length}]"
                                    id="oLockTarget"
                                    name="editval[oxArticle][oxarticles__oxtitle]"
                                    value="[{$oArticle->oxarticles__oxtitle->value}]"
                                    [{ $readonly }] />
                            [{ oxinputhelp ident="HELP_MF_PRODUCT_EXPORT_TITLE" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_MAIN_EAN" }]&nbsp;
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="32" maxlength="[{$oArticle__oxean->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxean]"
                                    value="[{$oArticle->oxarticles__oxean->value}]"
                                    [{ $readonly }]
                                    >
                            [{ oxinputhelp ident="HELP_MF_PRODUCT_EXPORT_EAN" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_MAIN_SHORTDESC" }]&nbsp;
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="32"
                                    maxlength="[{$oArticle->oxarticles__oxshortdesc->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxshortdesc]"
                                    value="[{$oArticle->oxarticles__oxshortdesc->value}]"
                                    [{ $readonly }]>
                            [{ oxinputhelp ident="HELP_MF_BEPADO_PRODUCT_EXPORT_SHORTDESCRIPTION" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_MAIN_VENDORID" }]
                        </td>
                        <td class="edittext">
                            <select class="editinput" name="editval[oxArticle][oxarticles__oxvendorid]" [{ $readonly }]>
                                <option value="" selected>---</option>
                                [{foreach from=$oView->getVendorList() item=oVendor}]
                                <option value="[{$oVendor->oxvendor__oxid->value}]"[{if $oArticle->oxarticles__oxvendorid->value == $oVendor->oxvendor__oxid->value}] selected[{/if}]>[{ $oVendor->oxvendor__oxtitle->value }]</option>
                                [{/foreach}]
                            </select>
                            [{ oxinputhelp ident="HELP_MF_BEPADO_PRODUCT_EXPORT_VENDORID" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_MAIN_PRICE" }] ([{ $oActCur->sign }])
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="8"
                                    maxlength="[{$oArticle->oxarticles__oxprice->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxprice]"
                                    value="[{$oArticle->oxarticles__oxprice->value}]"
                                    [{ $readonly }] />
                            [{assign var="oPrice" value=$oArticle->getPrice()}]
                            &nbsp;<em>( [{$oPrice->getBruttoPrice()}] )</em>
                            [{ oxinputhelp ident="HELP_ARTICLE_MAIN_PRICE" }]
                        </td>
                    </tr>
                    <tr>
                        [{assign var="purchasePriceField" value=$edit->oArticleHelper->getPurchasePriceField()}]
                        <td class="edittext">
                            [{ oxmultilang ident="MF_PRODUCT_EXPORT_PURCHASE_PRICE" }] ([{ $oActCur->sign }])
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="8"
                                    maxlength="[{$oArticle->$purchasePriceField->fldmax_length}]"
                                    name="editval[oxArticle][[{$purchasePriceField}]]"
                                    value="[{$oArticle->$purchasePriceField->value}]"
                                    [{ $readonly }]>
                            [{ oxinputhelp ident="HELP_MF_PRODUCT_EXPORT_PURCHASE_PRICE" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_STOCK_DELIVERY" }]
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="20"
                                    maxlength="[{$oArticle->oxarticles__oxdelivery->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxdelivery]"
                                    value="[{$oArticle->oxarticles__oxdelivery|oxformdate}]"
                                    [{include file="help.tpl" helpid=article_delivery}]
                                    [{ $readonly }]>
                            [{ oxinputhelp ident="HELP_ARTICLE_STOCK_DELIVERY" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext" colspan="2"><br><br>
                            <input
                                    type="submit"
                                    class="edittext"
                                    id="oLockButton"
                                    name="saveArticle"
                                    value="[{ oxmultilang ident="ARTICLE_MAIN_SAVE" }]"
                            onClick="Javascript:document.myedit.fnc.value='save'"
                            [{if !$oArticle->oxarticles__oxtitle->value && !$oxparentid }]disabled[{/if}]
                            [{ $readonly }]
                            >
                        </td>
                    </tr>
                </table>
            </td>
            <!-- Anfang rechte Seite -->
            <td valign="top" class="edittext" align="left" style="table-layout:fixed">
                <fieldset
                        title="[{ oxmultilang ident="MF_BEPADO_PRODUCT_EXPORT_ATTRIBUTES" }]"
                style="padding-left: 5px;">
                <legend>[{ oxmultilang ident="MF_BEPADO_PRODUCT_EXPORT_ATTRIBUTES" }]</legend><br>

                <table cellspacing="0" cellpadding="0" border="0" width="100%">
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_EXTEND_WEIGHT" }]
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="10"
                                    maxlength="[{$oArticle->oxarticles__oxweight->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxweight]"
                                    value="[{$oArticle->oxarticles__oxweight->value}]"
                                    [{ $readonly }]
                                    >
                            [{oxmultilang ident="ARTICLE_EXTEND_WEIGHT_UNIT"}]
                            [{ oxinputhelp ident="HELP_ARTICLE_EXTEND_WEIGHT" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_EXTEND_MASS" }]
                        </td>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_EXTEND_LENGTH" }]&nbsp;
                            <input
                                    type="text"
                                    class="editinput"
                                    size="3"
                                    maxlength="[{$oArticle->oxarticles__oxlength->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxlength]"
                                    value="[{$oArticle->oxarticles__oxlength->value}]"
                                    [{ $readonly }]>
                            [{oxmultilang ident="ARTICLE_EXTEND_DIMENSIONS_UNIT"}]
                            [{ oxmultilang ident="ARTICLE_EXTEND_WIDTH" }]&nbsp;
                            <input
                                    type="text"
                                    class="editinput"
                                    size="3"
                                    maxlength="[{$oArticle->oxarticles__oxlength->fldmax_width}]"
                                    name="editval[oxArticle][oxarticles__oxwidth]"
                                    value="[{$oArticle->oxarticles__oxwidth->value}]"
                                    [{ $readonly }]>
                            [{oxmultilang ident="ARTICLE_EXTEND_DIMENSIONS_UNIT"}]
                            [{ oxmultilang ident="ARTICLE_EXTEND_HEIGHT" }]&nbsp;
                            <input
                                    type="text"
                                    class="editinput"
                                    size="3"
                                    maxlength="[{$oArticle->oxarticles__oxlength->fldmax_height}]"
                                    name="editval[oxArticle][oxarticles__oxheight]"
                                    value="[{$oArticle->oxarticles__oxheight->value}]"
                                    [{ $readonly }]>
                            [{oxmultilang ident="ARTICLE_EXTEND_DIMENSIONS_UNIT"}]
                            [{ oxinputhelp ident="HELP_ARTICLE_EXTEND_MASS" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="ARTICLE_EXTEND_UNITQUANTITY" }]
                        </td>
                        <td class="edittext">
                            <input
                                    type="text"
                                    class="editinput"
                                    size="10"
                                    maxlength="[{$oArticle->oxarticles__oxunitquantity->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxunitquantity]"
                                    value="[{$oArticle->oxarticles__oxunitquantity->value}]"
                                    [{ $readonly }]>
                            &nbsp;&nbsp;&nbsp;&nbsp; [{ oxmultilang ident="ARTICLE_EXTEND_UNITNAME" }]:
                            [{if $oView->getUnitsArray()}]
                            <select name="editval[oxArticle][oxarticles__oxunitname]" onChange="JavaScript:processUnitInput( this, 'unitinput' )">
                                <option value="">-</option>
                                [{foreach from=$oView->getUnitsArray() key=sKey item=sUnit}]
                                [{assign var="sUnitSelected" value=""}]
                                [{if $oArticle->oxarticles__oxunitname->value == $sKey}]
                                [{assign var="blUseSelection" value=true}]
                                [{assign var="sUnitSelected" value="selected"}]
                                [{/if}]
                                <option value="[{$sKey}]" [{$sUnitSelected}]>[{$sUnit}]</option>
                                [{/foreach}]
                            </select> /
                            [{/if}]
                            <input
                                    type="text"
                                    id="unitinput"
                                    class="editinput"
                                    size="10"
                                    maxlength="[{$oArticle->oxarticles__oxunitname->fldmax_length}]"
                                    name="editval[oxArticle][oxarticles__oxunitname]"
                                    value="[{if !$blUseSelection}][{$oArticle->oxarticles__oxunitname->value}][{/if}]"
                                    [{if $blUseSelection}]disabled="true"[{/if}] [{include file="help.tpl" helpid=article_unit}]>
                            [{ oxinputhelp ident="HELP_ARTICLE_EXTEND_UNITQUANTITY" }]
                        </td>
                    </tr>
                </table>

                </fieldset>

                <fieldset
                        title="[{ oxmultilang ident="MF_PRODUCT_EXPORT_LONG_DESCRIPTION" }]"
                style="padding-left: 5px;">
                <legend>[{ oxmultilang ident="MF_PRODUCT_EXPORT_LONG_DESCRIPTION" }]</legend>
                [{ $editor }]
                </fieldset>
            </td>
        </tr>
        [{/if}]

        [{if $oxid == -1}]

        <input type="hidden" name="editval[export_to_bepado]" value="1">

        <tr>
            <td width="15"></td>
            <td valign="top" class="edittext">
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td colspan="2">
                            [{if $errorExportingArticle eq 1}]
                            <div class="errorbox">
                                <p>[{ oxmultilang ident=$errorMessage }]</p>
                                <p>[{ oxmultilang ident="MF_BEPADE_PRODUCT_VERIFY_ARTICLE" }]</p>
                            </div>
                            [{/if}]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext">
                            [{ oxmultilang ident="MF_BEPADO_PRODUCT_TO_EXPORT" }]
                        </td>
                        <td class="edittext">
                            <select class="editinput" name="editval[articleToExport]" [{ $readonly }]>
                                <option value="" selected>[{ oxmultilang ident="MF_BEPAPO_PRODUCT_EXPORT_CHOSE_ARTICLE" }]</option>
                                [{foreach from=$oView->getArticlesToExport() item=listItem}]
                                <option value="[{$listItem->oxarticles__oxid->value}]">
                                    [{ $listItem->pwrsearchval|oxtruncate:200:"..":false }]</option>
                                [{/foreach}]
                            </select>
                            [{ oxinputhelp ident="HELP_MF_BEPAPO_PRODUCT_EXPORT_CREATE_NEW" }]
                        </td>
                    </tr>
                    <tr>
                        <td class="edittext" colspan="2"><br><br>
                            <input
                                    type="submit"
                                    class="edittext"
                                    id="oLockButton"
                                    name="saveArticle"
                                    value="[{ oxmultilang ident="MF_BEPADO_PRODUCT_EXPORT_SAVE" }]"
                            onClick="Javascript:document.myedit.fnc.value='save'"
                            [{ $readonly }]
                            >
                        </td>
                    </tr>
                </table>
            <td></td>
        </tr>
        [{/if}]
    </form>
</table>

[{include file="bottomnaviitem.tpl"}]

[{include file="bottomitem.tpl"}]
