<?php

/**
 * The model class for an imported/exported product.
 * Each of them will have an oxArticle representation too.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfBepadoProduct extends oxBase
{
    const PRODUCT_STATE_NONE = 0;
    const PRODUCT_STATE_EXPORTED = 1;
    const PRODUCT_STATE_IMPORTED = 2;
    const DATABASE_BASE_STRING = 'mfbepadoproducts__';

    /**
     * As every bepado product simply is a representation of an oxid article, we will link them.
     *
     * @var oxArticle
     */
    private $oxArticle;

    public function __construct()
    {
        parent::init('mfbepadoproducts');
    }

    /**
     * Setter for the state of an bepado product.
     *
     * This state can only be 1 (exported) or 2 (imported).
     *
     * @param $state
     *
     * @return mfBepadoProduct
     */
    public function setState($state)
    {
        $states = array(
            self::PRODUCT_STATE_EXPORTED,
            self::PRODUCT_STATE_IMPORTED
        );
        if (!in_array($state, $states)) {
          throw new InvalidArgumentException(
              sprintf("Product state %s is not supported. Use one of %s.", $state, implode(',', $states))
          );
        }

        $this->_setFieldData(self::DATABASE_BASE_STRING.'state', $state);

        return $this;
    }

    /**
     * The state is casted to an integer.
     *
     * The method will answer with one of the allowed states only.
     *
     * @return int
     */
    public function getState()
    {
        $states = array(
            self::PRODUCT_STATE_EXPORTED,
            self::PRODUCT_STATE_IMPORTED
        );
        $state = (int) $this->getFieldData(self::DATABASE_BASE_STRING.'state');
        if (!in_array($state, $states)) {
            return self::PRODUCT_STATE_NONE;
        }

        return $state;
    }

    /**
     * Setter for the shopId of the related sdk product.
     *
     * @param int $shopId
     *
     * @return $this
     */
    public function setShopId($shopId)
    {
        $this->_setFieldData(self::DATABASE_BASE_STRING.'shop_id', $shopId);

        return $this;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'shop_id');
    }

    /**
     * The product Id in the source shop.
     *
     * @param $productId
     *
     * @return $this
     */
    public function setProductSourceId($productId)
    {
        $this->_setFieldData(self::DATABASE_BASE_STRING.'p_source_id', $productId);

        return $this;
    }

    /**
     * @return int
     */
    public function getProductSourceId()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'p_source_id');
    }

    /**
     * @param oxArticle $article
     */
    public function setOxArticle(oxArticle $article)
    {
        $this->oxArticle = $article;
    }

    /**
     * @return oxArticle
     */
    public function getOxArticle()
    {
        return $this->oxArticle;
    }
}
