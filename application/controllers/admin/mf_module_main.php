<?php

/**
 * @author Maria Haubner <maria.haubner@mayflower.de>
 */
class mf_module_main extends mf_module_main_parent
{
    public function render()
    {
        parent::render();

        return 'mf_module_main.tpl';
    }
}
