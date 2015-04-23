<?php

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
abstract class mfAbstractConvertChain extends mfAbstractConverter
{
    /**
     * Chain of converters.
     *
     * @var mfConverterInterface[]
     */
    protected $converters = array();

    /**
     * We need to initialize the chain.
     */
    public function __construct()
    {
        $this->initializeChain();
    }

    /**
     * Add your converters to the chain.
     */
    protected abstract function initializeChain();
}
