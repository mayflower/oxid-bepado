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

use Bepado\SDK\SDK;

/**
 * The module helper serves methods for the common use cases in the work the the module it self.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_module_helper extends mf_abstract_helper
{
    /**
     * @param mfBepadoConfiguration $configuration
     *
     * @return bool
     */
    public function verifyAtSdk(mfBepadoConfiguration $configuration)
    {
        /** @var SDK $sdk */
        $sdk = $this->getVersionLayer()->createNewObject('mf_sdk_helper')->instantiateSdk($configuration);

        try {
            $sdk->verifyKey($configuration->getApiKey());
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Based on the shop config this method creates the net price on its own,
     * cause the calculation got rounded in the oxPrice methods.
     *
     * @param oxPrice $oxPrice
     * @return float
     */
    public function createNetPrice(oxPrice $oxPrice) {
        $value = 0;

        if ($this->getVersionLayer()->getConfig()->getConfigParam('blEnterNetPrice')) {
            if ($oxPrice->isNettoMode()) {
                $value = $oxPrice->getNettoPrice();
            } else {
                $value = $oxPrice->getBruttoPrice();
            }
        } else {
            if ($oxPrice->isNettoMode()) {
                $value = $oxPrice->getNettoPrice()*100/($oxPrice->getVat()+100);
            } else {
                $value = $oxPrice->getBruttoPrice()*100/($oxPrice->getVat()+100);
            }
        }

        return $value;
    }
}
