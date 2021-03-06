<?php

require_once __DIR__ . '/../BaseTestCase.php';

use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mfOrderConverterTest extends BaseTestCase
{
    /**
     * @var mfOrderConverter
     */
    protected $converter;

    protected $sdkOrderValues = array(
        'orderShop'          => null,
        'providerShop'       => null,
        'reservationId'      => 'some-reservation-id',
        'localOrderId'       => 'local-id',
        'shippingCosts'      => 1.99,
        'grossShippingCosts' => 2.20,
        'deliveryAddress'    => null,
        'billingAddress'     => null,
        'shippingRule'       => null,
        'paymentType'        => 'test-payment-type',
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
        'oxorder__oxid'            => 'local-id',
        'oxorder__oxuserid'        => 'test-user',
        'oxorder__oxbillcompany'   => 'test company',
        'oxorder__oxbillmail'      => null,
        'oxorder__oxbillfname'     => 'test',
        'oxorder__oxbilllname'     => 'case',
        'oxorder__oxbillstreet'    => 'test street',
        'oxorder__oxbillstreetnr'  => 12,
        'oxorder__oxbilladdinfo'   => 'test add line',
        'oxorder__oxbillcity'      => 'test city',
        'oxorder__oxbillcountryid' => 'some-country-id',
        'oxorder__oxbillstateid'   => 'some-state-id',
        'oxorder__oxbillzip'       => 12345,
        'oxorder__oxbillfon'       => '0000 0000',
        'oxorder__oxdelcompany'    => 'test company',
        'oxorder__oxdelmail'       => null,
        'oxorder__oxdelfname'      => 'test',
        'oxorder__oxdellname'      => 'case',
        'oxorder__oxdelstreet'     => 'test street',
        'oxorder__oxdelstreetnr'   => 12,
        'oxorder__oxdeladdinfo'    => 'test add line',
        'oxorder__oxdelcity'       => 'test city',
        'oxorder__oxdelcountryid'  => 'some-country-id',
        'oxorder__oxdelstateid'    => 'some-state-id',
        'oxorder__oxdelzip'        => 12345,
        'oxorder__oxdelfon'        => '0000 0000',
        'oxorder__oxpaymentid'     => 'payment-id',
    );
    protected $oxPayment;
    protected $addressConverter;
    protected $oxDb;
    protected $articleHelper;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->converter = new mfOrderConverter();
        $this->converter->setVersionLayer($this->versionLayer);


        // mocks of oxid classes
        $this->oxPayment = $this->getMockBuilder('oxPayment')->disableOriginalConstructor()->getMock();
        $this->articleHelper = $this->getMockBuilder('mf_sdk_article_helper')->disableOriginalConstructor()->getMock();
        $this->oxPayment->expects($this->any())->method('load')->will($this->returnValue(true));
        $this->oxPayment->expects($this->any())->method('isLoaded')->will($this->returnValue(true));
        $this->oxPayment
            ->expects($this->any())
            ->method('getFieldData')
            ->with($this->equalTo('bepadopaymenttype'))
            ->will($this->returnValue('test-payment-type'));
        $this->oxPayment
            ->expects($this->any())
            ->method('buildSelectString')
            ->with($this->equalTo(array('bepadopaymenttype' => 'test-payment-type')))
            ->will($this->returnValue('some-sql'));
        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
        $this->oxDb
            ->expects($this->any())
            ->method('getOne')
            ->will($this->returnValue('payment-id'));

    }

    /**
     * @dataProvider provideSdkOrderValues
     */
    public function testConvertToSDKOrder($orderProperty, $orderValue, $testable = true)
    {
        /** @var oxOrder $oxOrder */
        $oxOrder = oxNew('oxorder');
        $oxOrder->assign($this->oxOrderValues);
        $sdkOrder = new Struct\Order();
        $this->converter->fromShopToBepado($oxOrder, $sdkOrder, array('reservationId' => 'some-reservation-id'));

        if ($testable) {
            $this->assertEquals($orderValue, $sdkOrder->$orderProperty);
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
            $testable = in_array($property, array('shippingCosts', 'grossShippingCosts', 'shippingRule')) ? false : true;
            if ('deliveryAddress' === $property || 'billingAddress' === $property) {
                $value = $this->generateSDKAddress();
                $value->email = null; // isn't set for billing and delivery address
            }

            $values[] = array($property, $value, $testable);
        }

        return $values;
    }

    public function testConvertToSDKOrderWithOrderItems()
    {
        /** @var oxOrder $oxOrder */
        $oxOrder = oxNew('oxorder');
        $oxOrder->assign($this->oxOrderValues);

        /** @var oxList $oxArticlesList */
        $oxArticlesList = oxNew('oxList');
        $oxArticlesList->init('oxBase', 'oxOrderArticle');

        $importedOrderArticle = $this->getMockBuilder('oxOrderArticle')->disableOriginalConstructor()->getMock();
        $uselessOrderArticle = $this->getMockBuilder('oxOrderArticle')->disableOriginalConstructor()->getMock();
        $importedArticle = $this->getMockBuilder('mf_bepado_oxarticle')->disableOriginalConstructor()->getMock();
        $expectedProduct = new Struct\Product(array('title' => 'test-product'));

        $this->articleHelper
            ->expects($this->at(0))
            ->method('isOrderArticleImported')
            ->will($this->returnValue(true));
        $this->articleHelper
            ->expects($this->at(1))
            ->method('isOrderArticleImported')
            ->will($this->returnValue(false));

        $this->articleHelper->expects($this->once())->method('computeSdkProduct')->will($this->returnValue($expectedProduct));
        $importedOrderArticle->expects($this->once())->method('getArticle')->will($this->returnValue($importedArticle));
        $importedOrderArticle->expects($this->once())
            ->method('getFieldData')
            ->with($this->equalTo('oxorderarticles__oxamount'))
            ->will($this->returnValue(5));

        $oxArticlesList->add($importedOrderArticle);
        $oxArticlesList->add($uselessOrderArticle);
        $oxOrder->setOrderArticleList($oxArticlesList);

        $order = new Struct\Order();
        $this->converter->fromShopToBepado($oxOrder, $order);
        $orderItem = array_shift($order->orderItems);
        $actualProduct = $orderItem->product;

        $this->assertEquals($expectedProduct->title, $actualProduct->title);
        $this->assertEquals(5, $orderItem->count);
    }

    /**
     * @dataProvider provideOxidOrderValues
     */
    public function testConvertToOxidOrder($orderFieldName, $orderFieldValue, $testable = true)
    {
        $sdkOrder = new Struct\Order();
        foreach ($this->sdkOrderValues as $property => $value) {
            if ('deliveryAddress' === $property || 'billingAddress' === $property) {
                $value = $this->generateSDKAddress();
            }
            $sdkOrder->$property = $value;
        }
        $oxOrder = oxNew('oxOrder');
        $this->converter->fromBepadoToShop($sdkOrder, $oxOrder);

        if ($testable) {
            $this->assertEquals($orderFieldValue, $oxOrder->getFieldData($orderFieldName));
        } else {
            $this->markTestIncomplete('Can not test for Property: '.$orderFieldName);
        }
    }

    /**
     * @return array
     */
    public function provideOxidOrderValues()
    {
        $values = array();

        foreach ($this->oxOrderValues as $fieldName => $fieldValue) {
            $testable = in_array($fieldName, array(
                'oxorder__oxuserid',
                'oxorder__oxdelcompany',
                'oxorder__oxdelmail',
                'oxorder__oxdelfname',
                'oxorder__oxdellname',
                'oxorder__oxdelstreet',
                'oxorder__oxdelstreetnr',
                'oxorder__oxdeladdinfo',
                'oxorder__oxdelcity',
                'oxorder__oxdelcountryid',
                'oxorder__oxdelstateid',
                'oxorder__oxdelzip',
                'oxorder__oxdelfon',
                'oxorder__oxbillcountryid',
                'oxorder__oxbillstateid'
            )) ? false : true;
            if ('oxorder__oxid' === $fieldName) {
                $fieldValue = null;
            }
            $values[] = array($fieldName, $fieldValue, $testable);
        }

        return $values;
    }

    protected function getObjectMapping()
    {
        return array(
            'oxpayment'                => $this->oxPayment,
            'mf_sdk_article_helper'    => $this->articleHelper,
        );
    }

    /**
     * @return Struct\Address
     */
    private function generateSDKAddress()
    {
        $address = new Struct\Address();
        foreach ($this->addressValues as $property => $value) {
            $address->$property = $value;
        }
        return $address;
    }
}
