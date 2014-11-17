[{$smarty.block.parent}]

<tr>
    <td class="edittext">
        [{ oxmultilang ident="BEPADO_CATEGORY" }]
    </td>
    <td class="edittext" colspan="2">
        <select name="editval[oxcategories__bepadocategory]" class="editinput" [{$readonly}]>
        [{foreach from=$cattree->aList item=pcat}]
            <option value="[{if $pcat->oxcategories__oxid->value}][{$pcat->oxcategories__oxid->value}][{else}]oxrootid[{/if}]" [{ if $pcat->selected}]SELECTED[{/if}]>
                [{ $pcat->oxcategories__oxtitle->value|oxtruncate:33:"..":true }]
            </option>
        [{/foreach}]
        </select>
        [{ oxinputhelp ident="HELP_BEPADO_CATEGORY" }]
    </td>
</tr>