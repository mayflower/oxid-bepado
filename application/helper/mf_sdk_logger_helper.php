<?php


class mf_sdk_logger_helper extends mf_abstract_helper
{
    /**
     * Log file name
     *
     * @var string
     */
    protected $_sFileName = 'BEPADO_LOG.txt';

    /**
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