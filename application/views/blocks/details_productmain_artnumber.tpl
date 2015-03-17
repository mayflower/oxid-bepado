[{$smarty.block.parent}]

[{if $oDetailsProduct->marketplace_shop}]
    <div class="smallFont">
        <img src="[{$oViewConf->getModuleUrl("bepado")}]application/out/img/bepado_in.png"
             alt="Bepado"
             height="13px"
             align="center"/>
        <b><a
                    href="[{$oDetailsProduct->marketplace_shop->url}]"
                    target="_blank"
                    title="[{ oxmultilang ident="HELP_MF_BEPADO_MARKETPLACE_HINT" }]"
                    >
                [{$oDetailsProduct->marketplace_shop->name}]
            </a>
        </b>
    </div>
[{/if}]
