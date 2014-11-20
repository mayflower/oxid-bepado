[{$smarty.block.parent}]

<tr>
    <td class="edittext">
        [{ oxmultilang ident="BEPADO_CATEGORY" }]
    </td>
    <td class="edittext" colspan="2">
        <input name="mf_editval[bepado_categories__oxid]]" class="editinput" type="text" value="[{$edit->oxcategories__oxid->value}]" hidden [{$readonly}]>
        <select name="mf_editval[bepado_categories__title]" class="editinput" style="max-width: 300px;"[{$readonly}]>
        <option value="">[{ oxmultilang ident="BEPADO_CATEGORY_SELECT" }]</option>
        [{foreach from=$googleCategories item=gCat}]
            <option value="[{$gCat}]" [{if $gCat == $bepardoCategory->bepado_categories__title->rawValue}]SELECTED[{/if}]>[{$gCat}]</option>
        [{/foreach}]
        </select>
        [{ oxinputhelp ident="HELP_BEPADO_CATEGORY" }]
    </td>
</tr>
