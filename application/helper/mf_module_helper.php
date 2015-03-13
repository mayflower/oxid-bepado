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
    const API_KEY_SETTING_NAME = 'sBepadoApiKey';

    const MODULE_ID = 'bepado';

    const API_URL_SETTING_NAME = 'sBepadoLocalEndpoint';

    /**
     * When saving the config variables, we will verify the settings.
     *
     * @param $_aConfParms
     *
     * @return bool
     */
    public function onSaveConfigVars($_aConfParms)
    {
        $oConfig = $this->getVersionLayer()->getConfig();
        /** @var mf_sdk_helper $sdkHelper */
        $sdkHelper = $this->getVersionLayer()->createNewObject('mf_sdk_helper');
        $sdkConfig = $sdkHelper->createSdkConfigFromOxid();

        foreach ($_aConfParms as $sType => $sParam) {
            $aConfVars = $oConfig->getRequestParameter($sParam);
            if (is_array($aConfVars)) {
                foreach ($aConfVars as $sName => $sValue) {
                    if (self::API_KEY_SETTING_NAME === $sName) {
                        $sdkConfig->setApiKey($sValue);
                    } elseif (self::API_URL_SETTING_NAME === $sName) {
                        $sdkConfig->setApiEndpointUrl($sValue);
                    }
                }
            }
        }

        if ($this->verifyAtSdk($sdkConfig)) {
            $isVerified = true;
        } else {
            $isVerified = false;
        }

        return $isVerified;
    }

    /**
     * @param mfBepadoConfiguration $config
     *
     * @return bool
     */
    public function verifyAtSdk(mfBepadoConfiguration $config)
    {
        /** @var SDK $sdk */
        $sdk = $this->getVersionLayer()->createNewObject('mf_sdk_helper')->instantiateSdk($config);

        try {
            $sdk->verifyKey($config->getApiKey());
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
