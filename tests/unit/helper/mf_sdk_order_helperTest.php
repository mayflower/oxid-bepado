<?php

use Bepado\SDK\Struct\OrderStatus;

require_once __DIR__ . '/../BaseTestCase.php';
require_once __DIR__ . '/../wrapper/sdkMock.php';

class mf_sdk_order_helperTest extends BaseTestCase
{

    protected $oxOrder;
    protected $oxOrderArticle;
    protected $oxArticle;

    protected $sdk;
    protected $mfSdkHelper;
    protected $mfArticleHelper;
    protected $mfLoggerHelper;

    /** @var  mf_sdk_order_helper */
    protected $helper;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->helper = new mf_sdk_order_helper();
        $this->helper->setVersionLayer($this->versionLayer);

        $this->oxOrder         = $this->getMockBuilder('oxOrder')->disableOriginalConstructor()->getMock();
        $this->oxOrderArticle  = $this->getMockBuilder('oxorderarticle')->disableOriginalConstructor()->getMock();
        $this->oxArticle       = $this->getMockBuilder('mf_bepado_oxarticle')->disableOriginalConstructor()->getMock();


        $this->sdk             = $this->getMockBuilder('sdkMock')->disableOriginalConstructor()->getMock();
        $this->mfSdkHelper     = $this->getMockBuilder('mf_sdk_helper')->disableOriginalConstructor()->getMock();
        $this->mfArticleHelper = $this->getMockBuilder('mf_sdk_article_helper')->disableOriginalConstructor()->getMock();
        $this->mfLoggerHelper  = $this->getMockBuilder('mf_sdk_logger_helper')->disableOriginalConstructor()->getMock();

        $sdkConfig = new SDKConfig();
        $this->mfSdkHelper
            ->expects($this->any())
            ->method('createSdkConfigFromOxid')
            ->will($this->returnValue($sdkConfig));
        $this->mfSdkHelper
            ->expects($this->any())
            ->method('instantiateSdk')
            ->with($this->equalTo($sdkConfig))
            ->will($this->returnValue($this->sdk));

        $this->oxOrder
            ->expects($this->once())
            ->method('getOrderArticles')
            ->will($this->returnValue(array($this->oxOrderArticle)));
        $this->oxOrder
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('test-id'));

        $this->oxOrderArticle
            ->expects($this->any())
            ->method('getArticle')
            ->will($this->returnValue($this->oxArticle));

        $this->mfArticleHelper
            ->expects($this->any())
            ->method('isArticleExported')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue(true));
    }

    public function testUpdateOrderStatus_Open()
    {
        $map = array(
            array('oxtransstatus', 'NOT_FINISHED'),
            array('oxpaid', '0000-00-00 00:00:00'),
            array('oxsenddate', ''),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));
        $this->oxOrder->expects($this->once())->method('save')->with($this->equalTo(false));

        $this->sdk->expects($this->never())->method('updateOrderStatus');

        $this->helper->checkForOrderStateUpdates($this->oxOrder);
    }

    public function testUpdateOrderStatus_justPayed()
    {
        $map = array(
            array('oxtransstatus', 'NOT_FINISHED'),
            array('mf_bepado_state', OrderStatus::STATE_OPEN),
            array('oxpaid', '2015-01-15 00:00:00'),
            array('oxsenddate', ''),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));
        $this->oxOrder->expects($this->once())->method('save')->with($this->equalTo(false));

        $expectedOrder = new OrderStatus();
        $expectedOrder->id = 'test-id';
        $expectedOrder->status = OrderStatus::STATE_IN_PROCESS;
        $message = new \Bepado\SDK\Struct\Message();
        $message->message = 'Provider shop has received payment on %payedDate';
        $message->values['payedDate'] = '2015-01-15 00:00:00';
        $expectedOrder->messages[] = $message;

        $this->sdk->expects($this->once())->method('updateOrderStatus')->with($this->equalTo($expectedOrder));

        $this->helper->checkForOrderStateUpdates($this->oxOrder);
    }

    public function testUpdateOrderStatus_deliveryDateJustSet()
    {
        $map = array(
            array('oxtransstatus', 'NOT_FINISHED'),
            array('mf_bepado_state', OrderStatus::STATE_IN_PROCESS),
            array('oxpaid', '2015-01-15 00:00:00'),
            array('oxsenddate', '2015-01-15 00:00:00'),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));
        $this->oxOrder->expects($this->once())->method('save')->with($this->equalTo(false));

        $expectedOrder = new OrderStatus();
        $expectedOrder->id = 'test-id';
        $expectedOrder->status = OrderStatus::STATE_DELIVERED;
        $message = new \Bepado\SDK\Struct\Message();
        $message->message = 'Provider shop has processed and delivered order on %senddate.';
        $message->values['senddate'] = '2015-01-15 00:00:00';
        $expectedOrder->messages[] = $message;

        $this->sdk->expects($this->once())->method('updateOrderStatus')->with($this->equalTo($expectedOrder));

        $this->oxidConfig
            ->expects($this->any())
            ->method('getRequestParameter')->with($this->equalTo('fnc'))
            ->will($this->returnValue('sendorder'));

        $this->helper->checkForOrderStateUpdates($this->oxOrder);
    }

    public function testUpdateOrderStatus_deliveryDateJustRemoved()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('isArticleExported')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue(true));

        $map = array(
            array('oxtransstatus', 'NOT_FINISHED'),
            array('mf_bepado_state', OrderStatus::STATE_DELIVERED),
            array('oxpaid', '2015-01-15 00:00:00'),
            array('oxsenddate', '2015-01-15 00:00:00'),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));
        $this->oxOrder->expects($this->once())->method('save')->with($this->equalTo(false));

        $expectedOrder = new OrderStatus();
        $expectedOrder->id = 'test-id';
        $expectedOrder->status = OrderStatus::STATE_ERROR;
        $message = new \Bepado\SDK\Struct\Message();
        $message->message = 'Provider shop removed the former order date';
        $expectedOrder->messages[] = $message;

        $this->sdk->expects($this->once())->method('updateOrderStatus')->with($this->equalTo($expectedOrder));

        $this->oxidConfig
            ->expects($this->any())
            ->method('getRequestParameter')->with($this->equalTo('fnc'))
            ->will($this->returnValue('resetorder'));

        $this->helper->checkForOrderStateUpdates($this->oxOrder);
    }

    public function testUpdateOrderStatus_deleted()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('isArticleExported')
            ->with($this->equalTo($this->oxArticle))
            ->will($this->returnValue(true));

        $map = array(
            array('oxtransstatus', 'NOT_FINISHED'),
            array('mf_bepado_state', OrderStatus::STATE_DELIVERED),
            array('oxpaid', '2015-01-15 00:00:00'),
            array('oxsenddate', '2015-01-15 00:00:00'),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));
        $this->oxOrder->expects($this->once())->method('save')->with($this->equalTo(false));

        $expectedOrder = new OrderStatus();
        $expectedOrder->id = 'test-id';
        $expectedOrder->status = OrderStatus::STATE_CANCELED;
        $message = new \Bepado\SDK\Struct\Message();
        $message->message = 'Provider shop canceled the order';
        $expectedOrder->messages[] = $message;

        $this->sdk->expects($this->once())->method('updateOrderStatus')->with($this->equalTo($expectedOrder));

        $this->helper->checkForOrderStateUpdates($this->oxOrder, true);
    }

    public function testUpdateOrderStatus_storno()
    {
        $map = array(
            array('oxtransstatus', 'NOT_FINISHED'),
            array('mf_bepado_state', OrderStatus::STATE_DELIVERED),
            array('oxpaid', '2015-01-15 00:00:00'),
            array('oxsenddate', '2015-01-15 00:00:00'),
            array('oxstorno', '1'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));
        $this->oxOrder->expects($this->once())->method('save')->with($this->equalTo(false));

        $expectedOrder = new OrderStatus();
        $expectedOrder->id = 'test-id';
        $expectedOrder->status = OrderStatus::STATE_CANCELED;
        $message = new \Bepado\SDK\Struct\Message();
        $message->message = 'Provider shop canceled the order';
        $expectedOrder->messages[] = $message;

        $this->sdk->expects($this->once())->method('updateOrderStatus')->with($this->equalTo($expectedOrder));

        $this->helper->checkForOrderStateUpdates($this->oxOrder);
    }

    protected function getObjectMapping()
    {
        return array(
            'SDKConfig'              => new SDKConfig(),
            'oxorder'                => $this->oxOrder,
            'mf_sdk_helper'          => $this->mfSdkHelper,
            'mf_sdk_article_helper'  => $this->mfArticleHelper,
            'mf_sdk_logger_helper'   => $this->mfLoggerHelper,
        );
    }
}
