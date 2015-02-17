<?php

/**
 * Test oxUtils module.
 */
class modOxUtils_order extends oxUtils
{

    /**
     * Throw an exeption instead of redirect to page.
     *
     * @param string  $sUrl               url
     * @param boolean $blAddRedirectParam add redirect param
     * @param integer $iHeaderCode        header code
     *
     * @return null
     */
    public function redirect($sUrl, $blAddRedirectParam = true, $iHeaderCode = 301)
    {
        throw new Exception($sUrl);
    }
}

/**
 * Test oxPayment module.
 */
class modOxPaymentIsValid_order extends oxPayment
{

    public static $dBasketPrice = null;

    /**
     * Skip isValidPayment and change $dBasketPrice.
     *
     * @param array  $aDynvalue    Dynamic values
     * @param string $sShopId      Shop id
     * @param object $oUser        User object
     * @param double $dBasketPrice Basket price
     * @param string $sShipSetId   Shipping set id
     *
     * @return boolean
     */
    public function isValidPayment($aDynvalue, $sShopId, $oUser, $dBasketPrice, $sShipSetId)
    {
        self::$dBasketPrice = $dBasketPrice;

        return true;
    }
}

/**
 * Test oxUtilsObject module.
 */
class modOxUtilsObject_order extends oxUtilsObject
{

    /**
     * Allways generate fixed uid.
     *
     * @return string
     */
    public function generateUID()
    {
        return "testUID";
    }
}

/**
 * Test oxOrdert module.
 */
class modOxOrder_order extends oxOrder
{

    /**
     * Skip finalizeOrder.
     *
     * @param object $oBasket              basket object
     * @param object $oUser                user object
     * @param bool   $blRecalculatingOrder Recalculating Order
     *
     * @return boolean
     */
    public function finalizeOrder(oxBasket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        return 1;
    }

    /**
     * Skip validateStock.
     *
     * @param object $oBasket basket object
     *
     * @return boolean
     */
    public function validateStock($oBasket)
    {
        return true;
    }
}

/**
 * Test oxPayment module.
 */
class modOxPayment_order extends oxPayment
{

    /**
     * Skip isValidPayment.
     *
     * @param array  $aDynvalue    Dynamic values
     * @param string $sShopId      Shop id
     * @param object $oUser        User object
     * @param double $dBasketPrice Basket price
     * @param string $sShipSetId   Shipping set id
     *
     * @return boolean
     */
    public function isValidPayment($aDynvalue, $sShopId, $oUser, $dBasketPrice, $sShipSetId)
    {
        return true;
    }
}

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_orderTest extends OxidTestCase
{
    protected $sdkHelper;

    public function setUp()
    {
        parent::setUp();
        oxTestModules::addFunction('oxSeoEncoderManufacturer', '_saveToDb', '{return null;}');

        $oUser = oxNew('oxUser');
        $oUser->setId('_testUserId');
        $oUser->save();

        oxAddClassModule('modOxUtils_order', 'oxutils');
        modOxPaymentIsValid_order::$dBasketPrice = null;
    }

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown()
    {
        //remove data from db
        $this->cleanUpTable('oxuser');
        $this->cleanUpTable('oxaddress');
        $this->cleanUpTable('oxobject2group', 'oxobjectid');

        oxRemClassModule('modOxUtils_order');
        oxRemClassModule('modOxUtilsObject_order');
        oxRemClassModule('modOxOrder_order');
        oxRemClassModule('modOxPayment_order');
        parent::tearDown();
    }

    public function testRender()
    {
        oxAddClassModule('modOxUtilsObject_order', 'oxutilsobject');
        oxAddClassModule('modOxPayment_order', 'oxpayment');

        $oConfig = oxRegistry::getConfig();
        $mySession = oxRegistry::getSession();

        //basket name in session will be "basket"
        $oConfig->setConfigParam('blMallSharedBasket', 1);
        $oConfig->setConfigParam('iMinOrderPrice', false);

        $oPrice = oxNew('oxPrice');
        $oPrice->setPrice(100, 19);

        $aBasketArticles = array(1, 2, 3);

        $oBasket = $this->getMock('oxBasket', array('getPrice', 'getProductsCount', 'getBasketArticles'));
        $oBasket->expects($this->any())->method('getPrice')->will($this->returnValue($oPrice));
        $oBasket->expects($this->any())->method('getProductsCount')->will($this->returnValue(1));
        $oBasket->expects($this->any())->method('getBasketArticles')->will($this->returnValue($aBasketArticles));

        //setting order info to session
        $oBasket->setPayment('oxidcashondel');
        oxRegistry::getSession()->setVariable('sShipSet', 'oxidstandard');
        $mySession->setBasket($oBasket);
        //oxRegistry::getSession()->setVariable( 'basket', $oBasket );
        oxRegistry::getSession()->setVariable('usr', 'oxdefaultadmin');
        oxRegistry::getSession()->setVariable('deladrid', 'null');
        oxRegistry::getSession()->setVariable('ordrem', 'testRemark');

        //setting some config data
        $oConfig->setConfigParam('blConfirmAGB', '1');
        $oConfig->setConfigParam('blConfirmCustInfo', '1');

        $oOrder = $this->getProxyClass("mf_order");

        $sResult = $oOrder->render();

        //checking return value
        $this->assertEquals('page/checkout/order.tpl', $sResult);
    }
}
