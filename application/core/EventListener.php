<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class EventListener
{
    /**
     * @var VersionLayerInterface
     */
    public static $_oVersionLayer;

    public static function onActivate()
    {
        /** @var mf_sdk_helper $helper */
        $helper = oxNew('mf_sdk_helper');
        $helper->onModuleActivation();
    }
}
