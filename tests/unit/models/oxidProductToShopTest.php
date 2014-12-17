<?php

require_once __DIR__.'/../BaseTestCase.php';

use Bepado\SDK\Struct as Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class oxidProductToShopTest extends BaseTestCase
{
    /**
     * @var oxidProductToShop
     */
    protected $productToShop;

    protected $sdkHelper;

    protected $converter;
    protected $bepadoProductState;
    protected $oxDb;
    protected $convertedOxArticle;
    protected $oxArticle;
    protected $oxBase;

    public function setUp()
    {
        $this->prepareVersionLayerWithConfig();

        $this->productToShop = new oxidProductToShop();
        $this->productToShop->setVersionLayer($this->versionLayer);

        // oxid classes
        $this->oxDb = $this->getMockBuilder('oxLegacyDb')->disableOriginalConstructor()->getMock();
        $this->convertedOxArticle = $this->getMockBuilder('mf_bepado_oxarticle')->disableOriginalConstructor()->getMock();
        $this->oxArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));

        // helper/converter for our module
        $this->sdkHelper = $this->getMock('mf_sdk_helper', array('createSdkConfigFromOxid'));
        $this->converter = $this->getMockBuilder('mf_sdk_converter')->disableOriginalConstructor()->getMock();
        $this->converter->expects($this->any())->method('fromBepadoToShop')->will($this->returnValue($this->convertedOxArticle));

        // create the bepadoProductState from that
        $this->bepadoProductState = $this->getMockBuilder('oxBase')->disableOriginalConstructor()->getMock();
        $this->bepadoProductState->expects($this->any())->method('init')->with($this->equalTo('bepado_product_state'));

        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
    }

    public function testInsertProduct()
    {
        $product = new Struct\Product();
        $product->sourceId = 'some-id';
        $product->shopId = 'shop-id';

        // expected method calls
        $this->bepadoProductState
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'some-id', 'shop_id' => 'shop-id')))
            ->will($this->returnValue('sql-query'))
            ;
        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('sql-query'))
            ->will($this->returnValue(false));
        $this->bepadoProductState
            ->expects($this->once())
            ->method('isLoaded')
            ->will($this->returnValue(false));

        // assign data and save the article
        $this->convertedOxArticle
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo(array(
                'oxarticles__oxactive' => 0,
                'oxarticles__oxstockflag' => 3,
            )));
        $this->convertedOxArticle->expects($this->once())->method('save');

        // create an entry for the state
        $this->convertedOxArticle->expects($this->once())->method('getId')->will($this->returnValue('test-id'));
        $this->bepadoProductState
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo(array(
                'p_source_id' => 'some-id',
                'shop_id'     => 'shop-id',
                'state'       => SDKConfig::ARTICLE_STATE_IMPORTED,
                'OXID'        => 'test-id',
            )));
        $this->bepadoProductState->expects($this->once())->method('save');

        // trigger the insert action
        $this->productToShop->insertOrUpdate($product);
    }

    public function testUpdateProduct()
    {
        $product = new Struct\Product();
        $product->sourceId = 'some-id';
        $product->shopId = 'shop-id';

        // expected method calls
        $this->bepadoProductState
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'some-id', 'shop_id' => 'shop-id')))
            ->will($this->returnValue('sql-query'))
        ;

        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('sql-query'))
            ->will($this->returnValue('test-id'));
        // so the state need to be loaded and should return true for the load state
        $this->bepadoProductState
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('test-id'));
        $this->bepadoProductState
            ->expects($this->once())
            ->method('isLoaded')
            ->will($this->returnValue(true));
        $this->bepadoProductState->expects($this->once())->method('getId')->will($this->returnValue('test-id'));
        // expected methods on the existing oxArticle
        $this->oxArticle
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('test-id'));
        $this->oxArticle->expects($this->once())->method('isLoaded')->will($this->returnValue(true));
        $this->convertedOxArticle
            ->expects($this->once())
            ->method('getFieldNames')
            ->will($this->returnValue(array('oxid', 'oxtitle')));
        $this->convertedOxArticle
            ->expects($this->any())
            ->method('getFieldData')
            ->will($this->returnValue('some-value'));
        // expected parameters to be assigned
        $this->oxArticle
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo(array(
                'oxarticles__oxtitle' => 'some-value',
            )));
        $this->oxArticle->expects($this->once())->method('save');
        // trigger the insert action
        $this->productToShop->insertOrUpdate($product);
    }

    public function testUpdateProductNotLoaded()
    {
        $product = new Struct\Product();
        $product->sourceId = 'some-id';
        $product->shopId = 'shop-id';

        // expected method calls
        $this->bepadoProductState
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'some-id', 'shop_id' => 'shop-id')))
            ->will($this->returnValue('sql-query'))
        ;

        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('sql-query'))
            ->will($this->returnValue('test-id'));
        // so the state need to be loaded and should return true for the load state
        $this->bepadoProductState
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('test-id'));
        $this->bepadoProductState
            ->expects($this->once())
            ->method('isLoaded')
            ->will($this->returnValue(true));
        $this->bepadoProductState->expects($this->once())->method('getId')->will($this->returnValue('test-id'));
        // expected methods on the existing oxArticle
        $this->oxArticle
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('test-id'));
        $this->oxArticle->expects($this->once())->method('isLoaded')->will($this->returnValue(false));

        // trigger the insert action
        $this->productToShop->insertOrUpdate($product);
    }

    public function testTransactionStart()
    {
        $this->oxDb
            ->expects($this->once())
            ->method('startTransaction');

        $this->productToShop->startTransaction();
    }

    public function testTransactionCommit()
    {
        $this->oxDb
            ->expects($this->once())
            ->method('commitTransaction');

        $this->productToShop->commit();
    }

    public function testDelete()
    {
        $this->bepadoProductState
            ->expects($this->once())
            ->method('buildSelectString')
            ->with(array('p_source_id' => 'source-id', 'shop_id' => 'shop-id'))
            ->will($this->returnValue('some-sql'));
        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('some-sql'))
            ->will($this->returnValue('test-id'))
        ;
        $this->bepadoProductState
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('test-id'))
            ->will($this->returnValue(true));
        $this->bepadoProductState->expects($this->once())->method('delete');
        $this->oxArticle->expects($this->once())->method('load')->with('test-id');
        $this->oxArticle->expects($this->once())->method('delete');

        $this->productToShop->delete('shop-id', 'source-id');
    }

    public function testDeleteWithNonMarkedArticle()
    {
        $this->bepadoProductState
            ->expects($this->once())
            ->method('buildSelectString')
            ->with(array('p_source_id' => 'source-id', 'shop_id' => 'shop-id'))
            ->will($this->returnValue('some-sql'));
        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('some-sql'))
            ->will($this->returnValue(false))
        ;
        $this->bepadoProductState
            ->expects($this->never())
            ->method('load')
            ->with($this->equalTo('test-id'))
            ->will($this->returnValue(true));
        $this->bepadoProductState->expects($this->never())->method('delete');

        $this->productToShop->delete('shop-id', 'source-id');
    }

    protected function getObjectMapping()
    {
        return array(
            'oxbase'           => $this->bepadoProductState,
            'oxarticle'        => $this->oxArticle,
            'mf_sdk_converter' => $this->converter,
            'mf_sdk_helper'    => $this->sdkHelper,
        );
    }
}
