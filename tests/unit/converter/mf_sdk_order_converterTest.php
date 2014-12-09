<?php

require_once __DIR__ . '/../BaseTestCase.php';

use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_order_converterTest extends BaseTestCase
{
    /**
     * @var mf_sdk_order_converter
     */
    protected $converter;

    protected $sdkOrderValues = array(
        'orderShop'       => null,
        'providerShop'    => null,
        'reservationId'   => 'some-reservation-id',
        'localOrderId'    => null,
        'shippingCosts'   => null,
        'deliveryAddress' => null,
        'billingAddress'  => null,

    );

    protected $addressValues = array(
        'company'               => 'test company',
        'firstName'             => 'test',
        'surName'               => 'case',
        'street'                => 'test street',
        'streetNumber'          => 12,
        'additionalAddressLine' => 'test add line',
        'city'                  => 'test city',
        'zip'                   => 12345,
        'phone'                 => '0000 0000',
        'email'                 => 'test-mail@mail-for-test.de',
    );

    protected $oxOrderValues = array(
        'oxorder__oxid'            => null,
        'oxorder__oxuserid'        => 'test-user',
        'oxorder__oxbillcompany'   => 'test company',
        'oxorder__oxbillmail'      => 'test-mail@mail-for-test.de',
        'oxorder__oxbillfname'     => 'test',
        'oxorder__oxbilllname'     => 'case',
        'oxorder__oxbillstreet'    => 'test street',
        'oxorder__oxbillstreetnr'  => '12',
        'oxorder__oxbilladdinfo'   => 'test add line',
        'oxorder__oxbillcity'      => 'test city',
        'oxorder__oxbillcountryid' => 'some-country-id',
        'oxorder__oxbillstateid'   => 'some-state-id',
        'oxorder__oxbillzip'       => 12345,
        'oxorder__oxbillfon'       => '0000 0000',
        'oxorder__oxdelcompany'   => 'test company',
        'oxorder__oxdelmail'      => 'test-mail@mail-for-test.de',
        'oxorder__oxdelfname'     => 'test',
        'oxorder__oxdellname'     => 'case',
        'oxorder__oxdelstreet'    => 'test street',
        'oxorder__oxdelstreetnr'  => '12',
        'oxorder__oxdeladdinfo'   => 'test add line',
        'oxorder__oxdelcity'      => 'test city',
        'oxorder__oxdelcountryid' => 'some-country-id',
        'oxorder__oxdelstateid'   => 'some-state-id',
        'oxorder__oxdelzip'       => 12345,
        'oxorder__oxdelfon'       => '0000 0000',
    );

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->converter = new mf_sdk_order_converter();
        $this->converter->setVersionLayer($this->versionLayer);

    }

    /**
     * @dataProvider provideSdkOrderValues
     */
    public function testConvertToSDKOrder($orderProperty, $orderValue, $testable = true)
    {
        $this->markTestSkipped('Do Address first');
        /** @var oxOrder $oxOrder */
        $oxOrder = oxNew('oxorder');
        $oxOrder->assign($this->oxOrderValues);

        $product = $this->converter->fromShoptoBepado($oxOrder);

        if ($testable) {
            $this->assertEquals($orderValue, $product->$orderProperty);
        } else {
            $this->markTestIncomplete('Can not test for Property: '.$orderProperty);
        }
    }

    /**
     * @return array
     */
    public function provideSdkOrderValues()
    {
        $values = array();

        foreach ($this->sdkOrderValues as $property => $value) {
            $testable = in_array($property, array()) ? false : true;

            $values[] = array($property, $value, $testable);
        }

        return $values;
    }

    /**
     * @dataProvider provideOxidOrderValues
     */
    public function testConvertToOxidOrder($orderProperty, $orderValue, $testable = true)
    {
        $this->markTestSkipped('Do Address first');
        $sdkOrder = new Struct\Order();
        foreach ($this->sdkOrderValues as $property => $value) {
            if ('deliveryAddress' === $property || 'billingAddress' === $property) {
                $value = $this->generateAddress();
            }
            $sdkOrder->$property = $value;
        }

        $product = $this->converter->fromBepadoToShop($sdkOrder);

        if ($testable) {
            $this->assertEquals($orderValue, $product->$orderProperty);
        } else {
            $this->markTestIncomplete('Can not test for Property: '.$orderProperty);
        }
    }

    /**
     * @return array
     */
    public function provideOxidOrderValues()
    {
        $values = array();

        foreach ($this->oxOrderValues as $property => $value) {
            $testable = in_array($property, array()) ? false : true;
            if ('deliveryAddress' === $property || 'billingAddress' === $property) {
                $value = $this->generateAddress();
            }
            $values[] = array($property, $value, $testable);
        }

        return $values;
    }

    protected function getObjectMapping()
    {
        return array();
    }

    /**
     * @return Struct\Address
     */
    private function generateAddress($type = 'oxaddress__ox')
    {
        $address = new Struct\Address();
        foreach ($this->addressMapping as $oxField => $sdkProperty) {
            if (isset($this->addressValues[$sdkProperty])) {
                $address->$sdkProperty = $this->addressValues[$sdkProperty];
            }
        }

        return $address;
    }
}
