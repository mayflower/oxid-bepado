<?php

use Bepado\SDK\ProductToShop;
use Bepado\SDK\Struct;

class oxidProductToShop implements ProductToShop
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

    public function insertOrUpdate(Struct\Product $product)
    {
    }

    public function delete($shopId, $sourceId)
    {
    }

    public function startTransaction()
    {
    }

    public function commit()
    {
    }


    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    private function getVersionLayer()
    {
        if (null == $this->_oVersionLayer) {
            /** @var VersionLayerFactory $factory */
            $factory = oxNew('VersionLayerFactory');
            $this->_oVersionLayer = $factory->create();
        }

        return $this->_oVersionLayer;
    }
}
