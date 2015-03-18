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
        [{include file="_formparams.tpl" cl="mfunit_list" lstrt=$lstrt actedit=$actedit oxid=$oxid fnc="" language=$actlang editlanguage=$actlang}]
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
            <colgroup>
                <col width="4%">
                <col width="40%">
                <col width="40%">
                <col width="10%">
                <col width="4%">
            </colgroup>
            <tr class="listitem">
                <td class="listheader first" height="15" width="30" align="center">
                    <a
                            href="Javascript:top.oxid.admin.setSorting( document.search, 'mfbepadounits', 'oxid', 'asc');document.search.submit();"
                            class="listheader">
                        [{oxmultilang ident="MF_BEPDO_OXID_UNIT_KEY"}]
                    </a>
                </td>
                <td class="listheader">
                    <a
                            href="Javascript:top.oxid.admin.setSorting( document.search, 'mfbepadounits', 'bepadounitkey', 'asc');document.search.submit();"
                            class="listheader">
                        [{oxmultilang ident="MF_BEPDO_UNIT_KEY"}]
                    </a>
                </td>
                <td class="listheader"></td>
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
                <td valign="top" class="[{ $listclass }]">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->mfbepadounits__oxid->value }]');" class="[{ $listclass}]">[{ $listitem->mfbepadounits__oxid->value }]</a>
                    </div>
                </td>
                <td valign="top" class="[{ $listclass }]">
                    <div class="listitemfloating">
                        <a href="Javascript:top.oxid.admin.editThis('[{ $listitem->mfbepadounits__oxid->value }]');" class="[{ $listclass}]">[{ $listitem->mfbepadounits__bepadounitkey->value }]</a>
                    </div>
                </td>
                <td class="[{ $listclass}]">
                    [{if !$readonly}]
                        <a
                                href="Javascript:top.oxid.admin.deleteThis('[{ $listitem->mfbepadounits__oxid->value }]');"
                                class="delete"
                                id="del.[{$_cnt}]"title="" [{include file="help.tpl" helpid=item_delete}]>

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
        parent.parent.sMenuSubItem = "[{ oxmultilang ident="mfunit_LIST_MENUSUBITEM" }]";
        parent.parent.sWorkArea    = "[{$_act}]";
        parent.parent.setTitle();
    }
</script>
</body>
</html>
