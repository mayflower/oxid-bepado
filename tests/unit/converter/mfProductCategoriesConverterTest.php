<?php

use Bepado\SDK\Struct;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductCategoriesConverterTest extends BaseTestCase
{
    protected $oxList;

    /**
     * @var mfProductCategoriesConverter
     */
    protected $converter;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $categoryOne = oxNew('oxBase');
        $categoryOne->init('bepado_categories');
        $categoryOne->assign(array(
            'bepado_categories__catnid' => 'one',
            'bepado_categories__title'  => 'Label of one'
        ));
        $categoryTwo = oxNew('oxBase');
        $categoryTwo->init('bepado_categories');
        $categoryTwo->assign(array(
            'bepado_categories__catnid' => 'two',
            'bepado_categories__title'  => 'Label of two'
        ));

        $this->oxList = $this->getMockBuilder('oxList')->disableOriginalConstructor()->getMock();
        $this->oxList
            ->expects($this->once())
            ->method('init')
            ->with($this->equalTo('oxbase'), $this->equalTo('bepado_categories'));
        $this->oxList->expects($this->once())->method('getBaseObject');
        $this->oxList->expects($this->once())->method('getList');
        $this->oxList
            ->expects($this->once())
            ->method('getArray')
            ->will($this->returnValue(array($categoryOne, $categoryTwo)));

        $this->converter = oxNew('mfProductCategoriesConverter');
        $this->converter->setVersionLayer($this->versionLayer);
    }

    public function testFromShopToBepado()
    {
        $oArticle = $this->getMockBuilder('oxArticle')->disableOriginalConstructor()->getMock();
        $oArticle->expects($this->once())->method('getCategoryIds')->will($this->returnValue(array('one')));
        $oProduct = new Struct\Product();

        $this->converter->fromShopToBepado($oArticle, $oProduct);

        $this->assertEquals(array('Label of one'), $oProduct->categories);
    }

    protected function getObjectMapping()
    {
        return array(
            'oxList' => $this->oxList
        );
    }
}
