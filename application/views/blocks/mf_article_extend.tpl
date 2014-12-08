[{$smarty.block.parent}]
[{if $no_bepado_import == 1}]
</table>

</fieldset>

<br><br>
<fieldset title="[{ oxmultilang ident="BEPADO_SETTINGS" }]" style="padding-left: 5px;">
<legend>[{ oxmultilang ident="BEPADO_SETTINGS" }][{ oxinputhelp ident="HELP_ARTICLE_BEPADO_SETTINGS" }]</legend><br>

<table cellspacing="0" cellpadding="0" border="0">

    <table>

        <tr>
            <td class="edittext" width="120">
                [{ oxmultilang ident="ARTICLE_MAIN_BEPADO" }]
            </td>
            <td class="edittext">
                <input type="hidden" name="editval[export_to_bepado]" value="0">
                <input class="edittext" type="checkbox" name="editval[export_to_bepado]" value='1' [{if $export_to_bepado == 1}]checked[{/if}] [{ $readonly }]>

            </td>
        </tr>
[{/if}]