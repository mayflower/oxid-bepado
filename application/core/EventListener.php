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
 * The hook for OXID events.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class EventListener
{
    /**
     * @var VersionLayerInterface
     */
    public static $_oVersionLayer;

    /**
     * Action on module activation.
     *
     * Will let the helper do the work like creating the databases, add the fields we are needing and so on.
     *
     * @throws Exception
     */
    public static function onActivate()
    {
        /** @var mf_sdk_helper $helper */
        $helper = oxNew('mf_sdk_helper');
        $helper->onModuleActivation();
    }
}
