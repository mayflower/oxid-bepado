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
 * This helper supports in the work with the own logging.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_logger_helper extends mf_abstract_helper
{
    /**
     * Log file name
     *
     * @var string
     */
    protected $_sFileName = 'BEPADO_LOG.txt';

    /**
     * Writes a log message the bepado log file.
     *
     * @param string $sLogTxt
     * @param array $values
     */
    public function writeBepadoLog($sLogTxt, $values = array())
    {
        // if shop is in productive mode, don't log
        $oShopConfig = $this->getVersionLayer()->getConfig();
        $oShop = $this->getVersionLayer()->createNewObject('oxshop');
        $oShop->load($oShopConfig->getShopId());
        $blProductive = $oShop->isProductiveMode();

        if(!$blProductive) {
            //We are most likely are already dealing with an exception so making sure no other exceptions interfere
            try {
                $sLogMsg = $this->getString($sLogTxt, $values) . "\n---------------------------------------------\n\n";
                $this->getVersionLayer()->getUtils()->writeToLog($sLogMsg, $this->_sFileName);
            } catch (Exception $e) {
                // do nothing
            }
        }
    }

    /**
     * Creates the string with information of the data, the message and a serialized value (optional).
     *
     * @param string $sLogTxt
     * @param array $values
     * @return string
     */
    private function getString($sLogTxt, $values)
    {
        $string =
            "(time: " . date('Y-m-d H:i:s') . "): " .
            $sLogTxt . "\n" .
            ($values ? "\n" . serialize($values) . "\n" : "");

        return $string;
    }
}
