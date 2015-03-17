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
            ->setShopHintInBasket(true)
            ->setPurchaseGroup('A')
        ;

        $configuration->save();

        $myDB = $this->getDb();
        /** @var Object_ResultSet $result */
        $result = $myDB->execute("SELECT * FROM mfbepadoconfiguration  WHERE OXID = 'some-shop'");
        $expectedResult = ['some-shop', null, '0', '1', '1', 'A'];

        $this->assertEquals($expectedResult, $result->fields);

        // check the getter
        $configuration = new mfBepadoConfiguration();
        $configuration->setApiEndpointUrl('some-url');
        $configuration->load('some-shop');

        $this->assertEquals('some-shop', $configuration->getId());
        $this->assertEquals('0', $configuration->getSandboxMode());
        $this->assertEquals('1', $configuration->getShopHintInBasket());
        $this->assertEquals('1', $configuration->getShopHintOnArticleDetails());
        $this->assertFalse($configuration->isInSandboxMode());
        $this->assertTrue($configuration->hastShopHintInBasket());
        $this->assertTrue($configuration->hastShopHintOnArticleDetails());
        $this->assertEquals('some-url', $configuration->getApiEndpointUrl());
        $this->assertEquals('A', $configuration->getPurchaseGroup());
    }
}
