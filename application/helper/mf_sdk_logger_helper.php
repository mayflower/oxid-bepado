<?php


class mf_sdk_logger_helper extends mf_abstract_helper
{
    /**
     * Log file path/name
     *
     * @var string
     */
    protected $_sFileName = 'BEPADO_LOG.txt';

    /**
     * @param /Exceptopn $oLogObj
     */
    public function writeBepadoLog($oLogObj)
    {
        //We are most likely are already dealing with an exception so making sure no other exceptions interfere
        try {
            $sLogMsg = $this->getString($oLogObj) . "\n---------------------------------------------\n";
            oxRegistry::getUtils()->writeToLog($sLogMsg, $this->_sFileName);
        } catch (Exception $e) {
        }
    }

    /**
     * @param Exception $oLogObj
     * @return string
     */
    private function getString($oLogObj)
    {
        $string = " (time: " . date('Y-m-d H:i:s') . "): [{$oLogObj->getCode()}]: {$oLogObj->getMessage()}\n\n";

        return $string;
    }
} 