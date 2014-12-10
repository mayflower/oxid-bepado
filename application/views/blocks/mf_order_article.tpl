
[{if $listitem->oxorderarticles__oxstorno->value == 1}]
    [{assign var="listclass" value=listitem3 }]
[{else}]
    [{assign var="listclass" value=listitem$blWhite }]
[{/if}]

<td valign="top" class="[{ $listclass}]">[{ if $listitem->oxorderarticles__oxstorno->value != 1 && !$listitem->isBundle() }]<input type="text" name="aOrderArticles[[{$listitem->getId()}]][oxamount]" value="[{ $listitem->oxorderarticles__oxamount->value }]" class="listedit">[{else}][{ $listitem->oxorderarticles__oxamount->value }][{/if}]</td>

<td valign="top" class="[{ $listclass}]" height="15">
    [{if $listitem->oxarticles__oxid->value}]
        <a href="Javascript:editThis('[{ $listitem->oxarticles__oxid->value}]');" class="[{ $listclass}]">
    [{/if}]
            [{ $listitem->oxorderarticles__oxartnum->value }]
            [{if ($listitem->oxorderarticles__imported->value)}]
                <img src="[{$oViewConf->getModuleUrl("bepado")}][{$edit->oxorder__importpic->value}]"
                     alt="Bepado"
                     height="15px"
                     align="center"/>
            [{/if}]
        </a>
</td>

<td valign="top" class="[{ $listclass}]">[{if $listitem->oxarticles__oxid->value}]<a href="Javascript:editThis('[{ $listitem->oxarticles__oxid->value }]');" class="[{ $listclass}]">[{/if}][{ $listitem->oxorderarticles__oxtitle->value|oxtruncate:20:""|strip_tags }]</a></td>
<td valign="top" class="[{ $listclass}]">[{ $listitem->oxorderarticles__oxselvariant->value }]</td>
<td valign="top" class="[{ $listclass}]">
    [{if $listitem->getPersParams()}]
    [{foreach key=sVar from=$listitem->getPersParams() item=aParam name=persparams}]
    [{if !$smarty.foreach.persparams.first}]&nbsp;&nbsp;,&nbsp;[{/if}]
    <em>
        [{if $smarty.foreach.persparams.first && $smarty.foreach.persparams.last}]
        [{ oxmultilang ident="GENERAL_LABEL" }]
        [{else}]
        [{$sVar}] :
        [{/if}]
        [{$aParam}]
    </em>
    [{/foreach}]
    [{/if}]
</td>
<td valign="top" class="[{ $listclass}]">[{ $listitem->oxorderarticles__oxshortdesc->value|oxtruncate:20:""|strip_tags }]</td>
[{if $edit->isNettoMode() }]
<td valign="top" class="[{ $listclass}]">[{ $listitem->getNetPriceFormated() }] <small>[{ $edit->oxorder__oxcurrency->value }]</small></td>
<td valign="top" class="[{ $listclass}]">[{ $listitem->getTotalNetPriceFormated() }] <small>[{ $edit->oxorder__oxcurrency->value }]</small></td>
[{else}]
<td valign="top" class="[{ $listclass}]">[{ $listitem->getBrutPriceFormated() }] <small>[{ $edit->oxorder__oxcurrency->value }]</small></td>
<td valign="top" class="[{ $listclass}]">[{ $listitem->getTotalBrutPriceFormated() }] <small>[{ $edit->oxorder__oxcurrency->value }]</small></td>
[{/if}]
<td valign="top" class="[{ $listclass}]">[{ $listitem->oxorderarticles__oxvat->value}]</td>
<td valign="top" class="[{ $listclass}]">[{if !$listitem->isBundle()}]<a href="Javascript:DeleteThisArticle('[{ $listitem->oxorderarticles__oxid->value }]');" class="delete" [{if $readonly }]onclick="JavaScript:return false;"[{/if}] [{include file="help.tpl" helpid=item_delete}]></a>[{/if}]</td>
<td valign="top" class="[{ $listclass}]">[{if !$listitem->isBundle()}]<a href="Javascript:StornoThisArticle('[{ $listitem->oxorderarticles__oxid->value }]');" class="pause" [{if $readonly }]onclick="JavaScript:return false;"[{/if}] [{include file="help.tpl" helpid=item_storno}]></a>[{/if}]</td>
