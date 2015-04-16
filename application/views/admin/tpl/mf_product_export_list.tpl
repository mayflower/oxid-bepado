[{include file="headitem.tpl" title="GENERAL_ADMIN_TITLE"|oxmultilangassign box="list"}]
[{assign var="where" value=$oView->getListFilter()}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
    [{else}]
    [{assign var="readonly" value=""}]
    [{/if}]

<script type="text/javascript">
    <!--
    window.onload = function ()
    {
        top.reloadEditFrame();
        [{ if $updatelist == 1}]
        top.oxid.admin.updateList('[{ $oxid }]');
        [{ /if}]
    }
    //-->
</script>


<div id="liste">
    <form name="search" id="search" action="[{ $oViewConf->getSelfLink() }]" method="post">
        [{include file="_formparams.tpl" cl="mf_product_export_list" lstrt=$lstrt actedit=$actedit oxid=$oxid fnc="" language=$actlang editlanguage=$actlang}]
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <colgroup>
                <col width="3%">
                <col width="94%">
                <col width="2%">
            </colgroup>
            <tr class="listitem">
                <td class="listheader first" height="15" width="30" align="center">
                    [{ oxmultilang ident="GENERAL_ACTIVTITLE" }]
                </td>
                <td class="listheader first" height="15" width="30" align="center" colspan="2">
                    [{oxmultilang ident="MF_BEPADO_ARTICLE_TITLE"}]
                </td>
            </tr>

            [{assign var="blWhite" value=""}]
            [{assign var="_cnt" value=0}]
            [{foreach from=$mylist item=listitem}]
            [{assign var="_cnt" value=$_cnt+1}]
            <tr id="row.[{$_cnt}]">
                [{if $listitem->blacklist == 1}]
                [{assign var="listclass" value=listitem3}]
                [{else}]
                [{assign var="listclass" value=listitem$blWhite}]
                [{/if}]
                [{if $listitem->mfbepadoproducts__oxid->value == $oxid}]
                [{assign var="listclass" value=listitem4 }]
                [{/if}]
                [{ assign var="oArticle" value=$listitem->getOxArticle() }]
                <td
                        valign="top"
                        class="[{ $listclass}][{ if $oArticle->oxarticles__oxactive->value == 1}] active[{/if}]"
                        height="15">
                    <div class="listitemfloating">&nbsp</a></div>
                </td>
                <td valign="top" class="[{ $listclass }]">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->mfbepadoproducts__oxid->value }]');" class="[{ $listclass}]">[{ $oArticle->oxarticles__oxtitle->value }]</a>
                    </div>
                </td>
                <td class="[{ $listclass}]">
                    [{if !$readonly}]
                    <a
                            href="Javascript:top.oxid.admin.deleteThis('[{ $listitem->mfbepadoproducts__oxid->value }]');"
                            class="delete"
                            id="del.[{$_cnt}]"
                            title="[{oxmultilang ident="MF_BEPADO_PRODUCT_EXPORT_DELETE"}]"
                            [{include file="help.tpl" helpid=item_delete}]>

                    </a>
                    [{/if}]
                </td>
            </tr>
            [{if $blWhite == "2"}]
            [{assign var="blWhite" value=""}]
            [{else}]
            [{assign var="blWhite" value="2"}]
            [{/if}]
            [{/foreach}]
            [{include file="pagenavisnippet.tpl" colspan="5"}]
        </table>
    </form>
</div>

[{include file="pagetabsnippet.tpl"}]

<script type="text/javascript">
    if (parent.parent)
    {   parent.parent.sShopTitle   = "[{$actshopobj->oxshops__oxname->getRawValue()|oxaddslashes}]";
        parent.parent.sMenuItem    = "[{ oxmultilang ident="MF_CONFIGURATION" }]";
        parent.parent.sMenuSubItem = "[{ oxmultilang ident="MF_PRODUCT_IMPORT_LIST_MENUSUBITEM" }]";
        parent.parent.sWorkArea    = "[{$_act}]";
        parent.parent.setTitle();
    }
</script>
</body>
</html>
