<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_configuration_module_extend extends oxAdminDetails
{
    public function render()
    {
        parent::render();
        return 'mf_configuration_module_extend.tpl';
    }
}
