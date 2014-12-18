<?php

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
    }

    public function testUpdateOrderStatusNoExportArticles()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(0));

        $this->helper->updateOrderStatus($this->oxOrder);
    }

    public function testUpdateOrderStatusSuccess()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

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

        $this->sdk->expects($this->once())->method('updateOrderStatus');

        $this->helper->updateOrderStatus($this->oxOrder);
    }

    public function testUpdateOrderStatusFail()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

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

        $exception = new \RuntimeException();
        $this->sdk->expects($this->once())->method('updateOrderStatus')->will($this->throwException($exception));

        $this->helper->updateOrderStatus($this->oxOrder);
    }

    public function testUpdateOrderStatusInProcess()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

        $map = array(
            array('oxtransstatus', 'OK'),
            array('oxpaid', '2014-12-12 12:30:30'),
            array('oxsenddate', ''),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));

        $this->sdk->expects($this->once())->method('updateOrderStatus');

        $this->helper->updateOrderStatus($this->oxOrder);
    }

    public function testUpdateOrderStatusDelivered()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

        $map = array(
            array('oxtransstatus', 'OK'),
            array('oxpaid', '2014-12-12 12:30:30'),
            array('oxsenddate', '2014-12-13 12:30:30'),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));

        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('fnc'))
            ->will($this->returnValue('sendorder'));

        $this->sdk->expects($this->once())->method('updateOrderStatus');

        $this->helper->updateOrderStatus($this->oxOrder);
    }

    public function testUpdateOrderStatusCanceled()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

        $map = array(
            array('oxtransstatus', 'OK'),
            array('oxpaid', '0000-00-00 00:00:00'),
            array('oxsenddate', ''),
            array('oxstorno', '1'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));

        $this->sdk->expects($this->once())->method('updateOrderStatus');

        $this->helper->updateOrderStatus($this->oxOrder);
    }

    public function testUpdateOrderStatusErrorFlag()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

        $map = array(
            array('oxtransstatus', 'OK'),
            array('oxpaid', '2014-12-12 12:30:30'),
            array('oxsenddate', ''),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));

        $this->sdk->expects($this->once())->method('updateOrderStatus');

        $this->helper->updateOrderStatus($this->oxOrder, 1);
    }

    public function testUpdateOrderStatusErrorInternal()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

        $map = array(
            array('oxtransstatus', 'NOT_FINISHED'),
            array('oxpaid', '2014-12-12 12:30:30'),
            array('oxsenddate', ''),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));

        $this->sdk->expects($this->once())->method('updateOrderStatus');

        $this->helper->updateOrderStatus($this->oxOrder);
    }

    public function testUpdateOrderStatusNoErrorFlag()
    {
        $this->mfArticleHelper
            ->expects($this->any())
            ->method('getArticleBepadoState')
            ->will($this->returnValue(1));

        $map = array(
            array('oxtransstatus', 'OK'),
            array('oxpaid', '2014-12-12 12:30:30'),
            array('oxsenddate', '2014-12-13 12:30:30'),
            array('oxstorno', '0'),
        );

        $this->oxOrder
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValueMap($map));

        $this->oxidConfig
            ->expects($this->once())
            ->method('getRequestParameter')
            ->with($this->equalTo('fnc'))
            ->will($this->returnValue('sendorder'));

        $this->helper->updateOrderStatus($this->oxOrder, 1);
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
