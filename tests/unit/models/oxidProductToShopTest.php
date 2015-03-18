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
    protected $bepadoProduct;
    protected $oxDb;
    protected $convertedOxArticle;
    protected $oxArticle;
    protected $articleNumberGenerator;
    protected $logger;

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
        $this->articleNumberGenerator = $this->getMockBuilder('mf_article_number_generator')->disableOriginalConstructor()->getMock();
        $this->sdkHelper = $this->getMock('mf_sdk_helper', array('computeConfiguration'));
        $this->converter = $this->getMockBuilder('mf_sdk_converter')->disableOriginalConstructor()->getMock();
        $this->converter->expects($this->any())->method('fromBepadoToShop')->will($this->returnValue($this->convertedOxArticle));
        $this->logger = $this->getMockBuilder('mf_sdk_logger_helper')->disableOriginalConstructor()->getMock();
        $this->bepadoProduct = $this->getMockBuilder('mfBepadoProduct')->disableOriginalConstructor()->getMock();

        $this->versionLayer->expects($this->any())->method('getDb')->will($this->returnValue($this->oxDb));
    }

    public function testInsertProduct()
    {
        $product = new Struct\Product();
        $product->sourceId = 'some-id';
        $product->shopId = 'shop-id';

        // expected method calls
        $this->bepadoProduct
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'some-id', 'shop_id' => 'shop-id')))
            ->will($this->returnValue('sql-query'))
            ;
        $this->oxDb->expects($this->once())->method('getOne')->with($this->equalTo('sql-query'))->will($this->returnValue(false));
        $this->bepadoProduct->expects($this->never())->method('load');
        $this->bepadoProduct->expects($this->any())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_NONE));

        // assign data and save the article
        $this->articleNumberGenerator
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('article-number'));
        $this->convertedOxArticle
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo(array(
                'oxarticles__oxactive' => 0,
                'oxarticles__oxstockflag' => 3,
                'oxarticles__oxartnum'    => 'article-number',
            )));
        $this->convertedOxArticle->expects($this->once())->method('save');

        // create an entry for the state
        $this->convertedOxArticle->expects($this->any())->method('getId')->will($this->returnValue('test-id'));
        $this->bepadoProduct
            ->expects($this->once())
            ->method('assign')
            ->with($this->equalTo(array(
                'p_source_id' => 'some-id',
                'shop_id'     => 'shop-id',
                'state'       => mfBepadoConfiguration::ARTICLE_STATE_IMPORTED,
                'OXID'        => 'test-id',
            )));
        $this->bepadoProduct->expects($this->once())->method('save');

        // trigger the insert action
        $this->productToShop->insertOrUpdate($product);
    }

    public function testUpdateProduct()
    {
        $product = new Struct\Product();
        $product->sourceId = 'some-id';
        $product->shopId = 'shop-id';

        // expected method calls
        $this->bepadoProduct
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
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct->expects($this->any())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_IMPORTED));
        $this->bepadoProduct->expects($this->once())->method('getId')->will($this->returnValue('test-id'));

        // the product state entry won't be changed on update
        $this->bepadoProduct->expects($this->never())->method('save');

        // the article number should be created on insert only
        $this->articleNumberGenerator->expects($this->never())->method('generate');

        // expected methods on the existing oxArticle
        $this->oxArticle->expects($this->once())->method('load')->with($this->equalTo('test-id'));
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

        // so the state need to be loaded and should return true for the load state
        $this->bepadoProduct
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'some-id', 'shop_id' => 'shop-id')))
            ->will($this->returnValue('sql-query'));
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct->expects($this->any())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_IMPORTED));
        $this->bepadoProduct->expects($this->once())->method('getId')->will($this->returnValue('test-id'));

        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('sql-query'))
            ->will($this->returnValue('test-id'));
        // expected methods on the existing oxArticle
        $this->oxArticle->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->oxArticle->expects($this->once())->method('isLoaded')->will($this->returnValue(false));

        // trigger the insert action
        $this->productToShop->insertOrUpdate($product);
    }

    public function testInsertOrUpdateWithExportedArticleInjectedLogs()
    {
        $product = new Struct\Product();
        $product->sourceId = 'some-id';
        $product->shopId = 'shop-id';
        $product->title = 'some-name';

        // expected method calls

        // so the state need to be loaded and should return true for the load state
        $this->bepadoProduct
            ->expects($this->once())
            ->method('buildSelectString')
            ->with($this->equalTo(array('p_source_id' => 'some-id', 'shop_id' => 'shop-id')))
            ->will($this->returnValue('sql-query'));
        $this->bepadoProduct->expects($this->once())->method('load')->with($this->equalTo('test-id'));
        $this->bepadoProduct->expects($this->any())->method('getState')->will($this->returnValue(mfBepadoProduct::PRODUCT_STATE_EXPORTED));
        $this->bepadoProduct->expects($this->never())->method('save');

        $this->oxDb
            ->expects($this->once())
            ->method('getOne')
            ->with($this->equalTo('sql-query'))
            ->will($this->returnValue('test-id'));

        $this->logger
            ->expects($this->once())
            ->method('writeBepadoLog')
            ->with(
                $this->equalTo(
                    'Somebody tried to insert or update a bepado product, which is marked as an exported oxArticle.'
                ),
                $this->equalTo(array('product' => array('id' => 'some-id', 'name' => 'some-name')))
            );

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
        $this->bepadoProduct
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
        $this->bepadoProduct
            ->expects($this->once())
            ->method('load')
            ->with($this->equalTo('test-id'))
            ->will($this->returnValue(true));
        $this->bepadoProduct->expects($this->once())->method('delete');
        $this->oxArticle->expects($this->once())->method('load')->with('test-id');
        $this->oxArticle->expects($this->once())->method('delete');

        $this->productToShop->delete('shop-id', 'source-id');
    }

    public function testDeleteWithNonMarkedArticle()
    {
        $this->bepadoProduct
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
        $this->bepadoProduct
            ->expects($this->never())
            ->method('load')
            ->with($this->equalTo('test-id'))
            ->will($this->returnValue(true));
        $this->bepadoProduct->expects($this->never())->method('delete');

        $this->productToShop->delete('shop-id', 'source-id');
    }

    protected function getObjectMapping()
    {
        return array(
            'mfBepadoProduct'                      => $this->bepadoProduct,
            'oxArticle'                   => $this->oxArticle,
            'mf_sdk_converter'            => $this->converter,
            'mf_sdk_helper'               => $this->sdkHelper,
            'mf_article_number_generator' => $this->articleNumberGenerator,
            'mf_sdk_logger_helper'        => $this->logger,
        );
    }
}
