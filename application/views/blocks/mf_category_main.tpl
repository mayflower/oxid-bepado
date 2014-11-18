[{$smarty.block.parent}]

<tr>
    <td class="edittext">
        [{ oxmultilang ident="BEPADO_CATEGORY" }]
    </td>
    <td class="edittext" colspan="2">
        <select name="editval[oxcategories__bepadocategory]" class="editinput" style="max-width: 300px;"[{$readonly}]>
        <option value="">[{ oxmultilang ident="BEPADO_CATEGORY_SELECT" }]</option>
        [{foreach from=$bepadoCategories item=bcat}]
            <option value="[{$bcat}]" [{if $bcat == $edit->oxcategories__bepadocategory->rawValue}]SELECTED[{/if}]>[{$bcat}]</option>
        [{/foreach}]
        </select>
        [{ oxinputhelp ident="HELP_BEPADO_CATEGORY" }]
    </td>
</tr>
