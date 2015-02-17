<?php
require_once __DIR__ . '/../BaseTestCase.php';

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mfBepadoTest extends BaseTestCase
{
    /**
     * @var mfBepado
     */
    protected $controller;
    protected $sdkHelper;

    public function setUp()
    {
        parent::prepareVersionLayerWithConfig();

        $this->controller = new mfBepado();
        $this->controller->setVersionLayer($this->versionLayer);
        $this->sdkHelper = $this->getMock('mf_sdk_helper');
    }

    public function testRequest()
    {
        $this->sdkHelper
            ->expects($this->once())
            ->method('handleRequest')
            ->will($this->returnValue('test-result'));

        $this->assertEquals('mf_sdk_result.tpl', $this->controller->render());
        $aViewData = $this->controller->getViewData();
        $this->assertEquals('test-result', $aViewData['sdk_result']);
    }

    protected function getObjectMapping()
    {
        return array(
            'mf_sdk_helper' => $this->sdkHelper,
        );
    }
}
