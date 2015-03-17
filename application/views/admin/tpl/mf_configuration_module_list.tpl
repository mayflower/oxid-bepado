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
        [{include file="_formparams.tpl" cl="mf_configuration_module_list" lstrt=$lstrt actedit=$actedit oxid=$oxid fnc="" language=$actlang editlanguage=$actlang}]
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <colgroup>
                <col width="3%">
                <col width="10%">
                <col width="45%">
                <col width="2%">
            </colgroup>
            <tr class="listitem">
                <td class="listheader first" height="15" width="30" align="center">
                    <a
                        href="Javascript:top.oxid.admin.setSorting( document.search, 'mfbepadoconfiguration', 'sandboxmode', 'asc');document.search.submit();"
                        class="listheader">
                        [{oxmultilang ident="MF_BEPDO_SANDBOXMODE"}]
                    </a>
                </td>
                <td class="listheader">
                    <a
                        href="Javascript:top.oxid.admin.setSorting( document.search, 'mfbepadoconfiguration', 'oxid', 'asc');document.search.submit();"
                        class="listheader">
                        [{oxmultilang ident="MF_BEPDO_SHOP_ID"}]
                    </a>
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
                [{if $listitem->mfbepadoconfiguration__oxid->value == $oxid}]
                [{assign var="listclass" value=listitem4 }]
                [{/if}]
                <td
                        valign="top"class="[{ $listclass}][{ if $listitem->mfbepadoconfiguration__sandboxmode->value == 1}] active[{/if}]" height="15">
                    <div class="listitemfloating">&nbsp</a></div>
                </td>
                <td valign="top" class="[{ $listclass }]">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->mfbepadoconfiguration__oxid->value }]');" class="[{ $listclass}]">[{ $listitem->mfbepadoconfiguration__oxid->value }]</a>
                    </div>
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
        parent.parent.sMenuSubItem = "[{ oxmultilang ident="MF_CONFIGURATION_MODULE_LIST_MENUSUBITEM" }]";
        parent.parent.sWorkArea    = "[{$_act}]";
        parent.parent.setTitle();
    }
</script>
</body>
</html>
