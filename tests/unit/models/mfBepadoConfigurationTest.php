<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfBepadoConfigurationTest extends OxidTestCase
{
    public function tearDown()
    {
        $this->cleanUpTable('mfbepadoconfiguration');
    }

    public function testSetterGetter()
    {
        $configuration = new mfBepadoConfiguration();
        $configuration->setId('some-shop');
        $configuration
            ->setSandboxMode(false)
            ->setShopHintOnArticleDetails(true)
            ->setShopHintInBasket(true)
        ;

        $configuration->save();

        $myDB = $this->getDb();
        /** @var Object_ResultSet $result */
        $result = $myDB->execute("SELECT * FROM mfbepadoconfiguration  WHERE OXID = 'some-shop'");
        $expectedResult = ['some-shop', null, null, 0, 1, 1];

        $this->assertEquals($expectedResult, $result->fields);

        // check the getter
        $configuration = new mfBepadoConfiguration();
        $configuration->load('some-shop');

        $this->assertEquals('some-shop', $configuration->getId());
        $this->assertEquals('0', $configuration->getSandboxMode());
        $this->assertEquals('1', $configuration->getShopHintInBasket());
        $this->assertEquals('1', $configuration->getShopHintOnArticleDetails());
        $this->assertFalse($configuration->isInSandboxMode());
        $this->assertTrue($configuration->hastShopHintInBasket());
        $this->assertTrue($configuration->hastShopHintOnArticleDetails());
    }
}
