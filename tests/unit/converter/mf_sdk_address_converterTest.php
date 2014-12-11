<?php

require_once __DIR__ . '/../BaseTestCase.php';

use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_address_converterTest extends BaseTestCase
{

    protected $oxState;
    protected $oxCountry;

    /**
     * @var mf_sdk_address_converter
     */
    protected $converter;
    protected $oxDb;
    protected $sdkAddressValues = array(
        'company'               => 'test company',
        'firstName'             => 'test',
        'surName'               => 'case',
        'street'                => 'test street',
        'streetNumber'          => 12,
        'additionalAddressLine' => 'test add line',
        'city'                  => 'test city',
        'zip'                   => 12345,
        'phone'                 => '0000 0000',
        'country'               => 'TLA',
        'state'                 => 'Teststate'
    );
    protected $oxAddressValues = array(
        'oxaddress__oxcompany'   => 'test company',
        'oxaddress__oxfname'     => 'test',
        'oxaddress__oxlname'     => 'case',
        'oxaddress__oxstreet'    => 'test street',
        'oxaddress__oxstreetnr'  => 12,
        'oxaddress__oxaddinfo'   => 'test add line',
        'oxaddress__oxcity'      => 'test city',
        'oxaddress__oxcountryid' => 'some-country-id',
        'oxaddress__oxstateid'   => 'some-state-id',
        'oxaddress__oxzip'       => 12345,
        'oxaddress__oxfon'       => '0000 0000',

    );

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->converter = new mf_sdk_address_converter();
        $this->converter->setVersionLayer($this->versionLayer);

        // mocks of oxid classes
        $this->oxCountry = $this->getMockBuilder('oxCountry')->disableOriginalConstructor()->getMock();
        $this->oxState = $this->getMockBuilder('oxState')->disableOriginalConstructor()->getMock();
        $this->oxCountry
            ->expects($this->any())
            ->method('buildSelectString')
            ->with($this->equalTo(array('OXISOALPHA3' => 'TLA', 'OXACTIVE' => 1)))
            ->will($this->returnValue('some-sql-string-country'));
        $this->oxCountry->expects($this->any())->method('isLoaded')->will($this->returnValue(true));
        $this->oxCountry->expects($this->any())->method('getFieldData')->with($this->equalTo('oxisoalpa3'))->will($this->returnValue('TLA'));
        $this->oxState
            ->expects($this->any())
            ->method('buildSelectString')
            ->with($this->equalTo(array('OXTITLE' => 'Teststate')))
            ->will($this->returnValue('some-sql-string-state'));
        $this->oxState->expects($this->any())->method('isLoaded')->will($this->returnValue(true));
        $this->oxState->expects($this->any())->method('getFieldData')->with('oxtitle')->will($this->returnValue('Teststate'));
        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
        $this->oxDb
            ->expects($this->any())
            ->method('getOne')
            ->will($this->returnCallback(function ($argument) {
                return 'some-sql-string-country' === $argument ? 'some-country-id' : 'some-state-id';
            }));
    }

    /**
     * @dataProvider provideSdkAddressValues
     */
    public function testConvertToSDKAddress($addressProperty, $addressValue, $testable = true)
    {
        /** @var oxOrder $oxAddress */
        $oxAddress = oxNew('oxAddress');
        $oxAddress->assign($this->oxAddressValues);

        $address = $this->converter->fromShoptoBepado($oxAddress);

        if ($testable) {
            $this->assertEquals($addressValue, $address->$addressProperty);
        } else {
            $this->markTestIncomplete('Can not test for Property: '.$addressProperty);
        }
    }

    /**
     * @return array
     */
    public function provideSdkAddressValues()
    {
        $values = array();

        foreach ($this->sdkAddressValues as $property => $value) {
            $testable = in_array($property, array()) ? false : true;
            $values[] = array($property, $value, $testable);
        }

        return $values;
    }

    /**
     * @dataProvider provideOxidAddressValues
     */
    public function testConvertToOxidAddress($addressFieldName, $addressFieldValue, $testable = true)
    {
        $sdkAddress = new Struct\Address();
        foreach ($this->sdkAddressValues as $property => $value) {
            $sdkAddress->$property = $value;
        }

        /** @var oxAddress $oxAddress */
        $oxAddress = $this->converter->fromBepadoToShop($sdkAddress);

        if ($testable) {
            $this->assertEquals($addressFieldValue, $oxAddress->getFieldData($addressFieldName));
        } else {
            $this->markTestIncomplete('Can not test for Property: '.$addressFieldName);
        }
    }

    /**
     * @return array
     */
    public function provideOxidAddressValues()
    {
        $values = array();

        foreach ($this->oxAddressValues as $property => $value) {
            $testable = in_array($property, array()) ? false : true;
            $values[] = array($property, $value, $testable);
        }

        return $values;
    }

    public function testConvertToSDKAddressWithArrayExpected()
    {
        // prepare a fictional object with some address data
        $parameters = array();
        foreach ($this->oxAddressValues as $fieldname => $fieldValue) {
            $parameters[str_replace('oxaddress__ox', 'oxorder__oxbill', $fieldname)] = $fieldValue;
        }
        $oxOrder = oxNew('oxOrder');
        $oxOrder->assign($parameters);

        // create the expected Address
        $expectedAddress = new Struct\Address();
        foreach ($this->sdkAddressValues as $property => $value) {
            $expectedAddress->$property = $value;
        }

        $actualAddress = $this->converter->fromShoptoBepado($oxOrder, 'oxorder__oxbill');

        $this->assertEquals($expectedAddress, $actualAddress);
    }

    public function testConvertToOxAddressParameters()
    {
        $sdkAddress = new Struct\Address();
        foreach ($this->sdkAddressValues as $property => $value) {
            $sdkAddress->$property = $value;
        }

        // create the expected parameters
        $expectedParameters = array();
        foreach ($this->oxAddressValues as $fieldname => $fieldValue) {
            $expectedParameters[str_replace('oxaddress__ox', 'oxorder__oxbill', $fieldname)] = $fieldValue;
        }

        $actualParemeters = $this->converter->fromBepadoToShop($sdkAddress, 'oxorder__oxbill');

        $this->assertEquals($expectedParameters, $actualParemeters);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxstate'   => $this->oxState,
            'oxcountry' => $this->oxCountry,
            'oxaddress' => oxNew('oxaddress'),
        );
    }
}
