[{assign var="iPayError" value=$oView->getPaymentError() }]
[{if $iPayError == 1}]
    <div class="status error">[{ oxmultilang ident="ERROR_MESSAGE_COMPLETE_FIELDS_CORRECTLY" }]</div>
    [{elseif $iPayError == 2}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_PAYMENT_AUTHORIZATION_FAILED" }]</div>
    [{elseif $iPayError == 4}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_UNAVAILABLE_SHIPPING_METHOD" }]</div>
    [{elseif $iPayError == 5}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT" }]</div>
    [{elseif $iPayError == 6}]
    <div class="status error">[{ oxmultilang ident="TRUSTED_SHOP_UNAVAILABLE_PROTECTION" }]</div>
    [{elseif $iPayError == 9}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_BEPADO_RESPONSE_ORDER_PROBLEM_STATE" }]</div>

    [{elseif $iPayError > 6 && $iPayError != 9}]
    <!--Add custom error message here-->
    <div class="status error">[{ oxmultilang ident="MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT" }]</div>
    [{elseif $iPayError == -1}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_PAYMENT_UNAVAILABLE_PAYMENT_ERROR" suffix="COLON" }] "[{ $oView->getPaymentErrorText() }]").</div>
    [{elseif $iPayError == -2}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_NO_SHIPPING_METHOD_FOUND" }]</div>
    [{elseif $iPayError == -3}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_PAYMENT_SELECT_ANOTHER_PAYMENT" }]</div>
    [{elseif $iPayError == -4}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_PAYMENT_BANK_CODE_INVALID" }]</div>
    [{elseif $iPayError == -5}]
    <div class="status error">[{ oxmultilang ident="MESSAGE_PAYMENT_ACCOUNT_NUMBER_INVALID" }]</div>
    [{/if}]
