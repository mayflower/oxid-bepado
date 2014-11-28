<?php


class mf_order extends mf_order_parent
{
    /**
     * @var mf_product_helper
     */
    private $_oProductHelper;

    public function render()
    {
        $parent = parent::render();

        $oxBasket = $this->_aViewData['oxcmp_basket'];

        $this->getProductHelper()->checkProductsWithBepado($oxBasket);

        return $parent;
    }

    /**
     * @return mf_product_helper
     */
    private function getProductHelper()
    {
        if ($this->_oProductHelper === null) {
            $this->_oProductHelper = oxNew('mf_product_helper');
        }

        return $this->_oProductHelper;
    }
} 