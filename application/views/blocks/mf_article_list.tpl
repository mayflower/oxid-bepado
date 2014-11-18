[{if $listitem->blacklist == 1}]
[{assign var="listclass" value=listitem3 }]
[{else}]
[{assign var="listclass" value=listitem$blWhite }]
[{/if}]
[{if $listitem->oxarticles__oxid->value == $oxid }]
[{assign var="listclass" value=listitem4 }]
[{/if}]
<td valign="top" class="[{ $listclass}][{if $listitem->oxarticles__oxactive->value == 1}] active[{/if}]" height="15">
    <div class="listitemfloating">
        [{if $listitem->oxarticles__exporttobepado->value == 1}]
            <img src="[{$oViewConf->getModuleUrl("bepado")}]application/out/src/img/bepado.png"
                 alt="Bepado"
                 height="13px"
                 align="center"/>
        [{else}]
            &nbsp
        [{/if}]
    </div>
</td>
<td valign="top" class="[{ $listclass}]">
    <div class="listitemfloating"><a
                href="Javascript:top.oxid.admin.editThis('[{ $listitem->oxarticles__oxid->value }]');"
                class="[{ $listclass}]">[{ $listitem->oxarticles__oxartnum->value }]</a></div>
</td>
<td valign="top" class="[{ $listclass}]" height="15">
    <div class="listitemfloating">&nbsp;<a
                href="Javascript:top.oxid.admin.editThis('[{ $listitem->oxarticles__oxid->value }]');"
                class="[{ $listclass}]">[{ $listitem->pwrsearchval|oxtruncate:200:"..":false }]</a></div>
</td>
<td valign="top" class="[{ $listclass}]">
    <div class="listitemfloating"><a
                href="Javascript:top.oxid.admin.editThis('[{ $listitem->oxarticles__oxid->value }]');"
                class="[{ $listclass}]">[{ $listitem->oxarticles__oxshortdesc->value|strip_tags|oxtruncate:45:"..":true
            }]</a></div>
</td>
<td class="[{ $listclass}]">
    [{if !$readonly}]
        <a href="Javascript:top.oxid.admin.deleteThis('[{ $listitem->oxarticles__oxid->value }]');"
           class="delete"
           id="del.[{$_cnt}]"
           title="" [{include file="help.tpl" helpid=item_delete}]></a>
    [{/if}]
</td>