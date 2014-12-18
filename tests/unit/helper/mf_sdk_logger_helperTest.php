<?php

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../wrapper/sdkMock.php';

class mf_sdk_logger_helperTest extends BaseTestCase
{

    protected $oxShop;
    protected $oxUtils;

    /** @var  mf_sdk_logger_helper */
    protected $helper;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->helper = new mf_sdk_logger_helper();
        $this->helper->setVersionLayer($this->versionLayer);

        $this->oxShop = $this->getMockBuilder('oxShop')->disableOriginalConstructor()->getMock();
        $this->oxUtils = $this->getMockBuilder('oxUtils')->disableOriginalConstructor()->getMock();

        $this->versionLayer
            ->expects($this->any())
            ->method('getUtils')
            ->will($this->returnValue($this->oxUtils));
    }

    public function testWriteToBepadoLogProductive()
    {
        $this->oxShop
            ->expects($this->once())
            ->method('isProductiveMode')
            ->will($this->returnValue(true));

        $this->helper->writeBepadoLog('test');
    }

    public function testWriteToBepadoLogNotProductiveSuccess()
    {
        $this->oxShop
            ->expects($this->any())
            ->method('isProductiveMode')
            ->will($this->returnValue(false));
        $this->oxUtils
            ->expects($this->any())
            ->method('writeToLog');

        $this->helper->writeBepadoLog('test');
        $this->helper->writeBepadoLog('test', array('value_1', 'value_2'));
    }

    public function testWriteToBepadoLogNotProductiveFail()
    {
        $exception = new \RuntimeException();
        $this->oxShop
            ->expects($this->once())
            ->method('isProductiveMode')
            ->will($this->returnValue(false));
        $this->oxUtils
            ->expects($this->once())
            ->method('writeToLog')
            ->will($this->throwException($exception));

        $this->helper->writeBepadoLog('test');
    }

    protected function getObjectMapping()
    {
        return array(
            'oxshop'                 => $this->oxShop,
        );
    }
} 