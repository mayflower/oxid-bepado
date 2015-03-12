<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_module_main extends oxAdminDetails
{
    public function render()
    {
        parent::render();
        return 'mf_configuration_module_main.tpl';
    }
}
