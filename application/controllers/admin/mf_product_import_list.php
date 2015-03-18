<?php

/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * This controller will render the list view of the imported products admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mf_product_import_list extends mf_product_admin_list
{
    /**
     * Which is the base model to use for this list.
     *
     * @var string
     */
    protected $_sListClass = 'mfBepadoProduct';

    /**
     * Decides which template is chose for this list.
     *
     * @var string
     */
    protected $_sThisTemplate = 'mf_product_import_list.tpl';

    /**
     * We need to enrich the data of the mfBepadoProduct model by its
     * oxid article representation.
     */
    public function render()
    {
        $sParent = parent::render();

        $this->_aViewData['mylist'] = $this->filterArticlesByState(
            $this->_aViewData['mylist'],
            mfBepadoProduct::PRODUCT_STATE_IMPORTED
        );

        return $sParent;
    }
}
