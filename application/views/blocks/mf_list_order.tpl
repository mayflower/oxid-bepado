
<td class="listitem[{ $blWhite }]">
    <a href="Javascript:editThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">
        [{ $oOrder->oxorder__oxorderdate|oxformdate }]
    </a>
</td>
<td class="listitem[{ $blWhite }]">
    <a href="Javascript:editThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">
        [{ $oOrder->oxorder__oxartnum->value }]
        [{if ($oOrder->oxorder__importpic->value)}]
            <img src="[{$oViewConf->getModuleUrl("bepado")}][{$oOrder->oxorder__importpic->value}]"
                 alt="Bepado"
                 height="15px"
                 align="center"/>
        [{/if}]
    </a>
</td>
<td class="listitem[{ $blWhite }]">
    <a href="Javascript:editThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">
        [{ $oOrder->oxorder__oxorderamount->value }]
    </a>
</td>
<td class="listitem[{ $blWhite }]">
    <a href="Javascript:editThis( '[{ $oOrder->oxorder__oxorderid->value }]');" class="listitem[{ $blWhite }]">
        [{ $oOrder->oxorder__oxtitle->getRawValue() }]
    </a>
</td>
<td class="listitem[{ $blWhite }]"><a href="Javascript:editThis( '[{ $oOrder->oxorder__oxorderid->value }]');"
