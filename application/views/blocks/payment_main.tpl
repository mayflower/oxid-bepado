[{$smarty.block.parent}]

<tr>
    <td class="edittext" width="100">
        [{ oxmultilang ident="BEPADO_PAYMENT_TYPE" }]
    </td>
    <td class="edittext">
        [{ assign var="payment_types" value = ','|explode:"advance, invoice, debit, creditcard, provider, other, unknown" }]
        <select name="editval[oxpayments__bepadopaymenttype]" class="editinput">
        [{foreach from=$payment_types item=type}]
            <option value="[{ $type }]" [{ if $type == $edit->oxpayments__bepadopaymenttype->value}]SELECTED[{/if}]>[{oxmultilang ident="BEPADO_PAYMENT_TYPE_$type"}]</option>
        [{/foreach}]
        </select>
        [{ oxinputhelp ident="HELP_BEPADO_PAYMENT_TYPE" }]
    </td>
</tr>