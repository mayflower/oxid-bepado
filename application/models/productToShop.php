<?php
use Bepado\SDK\ProductFromShop;
use Bepado\SDK\Struct;

class oxidProductToShop implements ProductToShop
{
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
}
