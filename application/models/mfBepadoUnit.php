<?php
use Bepado\SDK\Units;

/**
 * The model class for the oxid bepado mapping of the units.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfBepadoUnit extends oxBase
{
    const DATABASE_BASE_STRING = 'mfbepadounits__';
    const DEFAULT_BEPADO_UNIT_KEY = 'piece';

    /**
     * Little mapper for the units to support the guesser.
     *
     * @var array
     */
    private $oxidUnitMapper = array(
        '_UNIT_KG' => 'kg',
        '_UNIT_G' => 'g',
        '_UNIT_L' => 'l',
        '_UNIT_ML' => 'ml',
        '_UNIT_CM' => 'cm',
        '_UNIT_MM' => 'mm',
        '_UNIT_M' => 'm',
        '_UNIT_M2' => 'm^2',
        '_UNIT_M3' => 'm^3',
        '_UNIT_PIECE' => 'piece',
        '_UNIT_ITEM' => 'piece',
    );

    public function __construct()
    {
        parent::init('mfbepadounits');
    }

    /**
     * Will return the current bepado key.
     */
    public function getBepadoKey()
    {
        return $this->getFieldData(self::DATABASE_BASE_STRING.'bepadounitkey');
    }

    /**
     * Will set the api key to the configuration.
     *
     * @param string $bepadoKey
     *
     * @return mfBepadoUnit
     */
    public function setBepadoKey($bepadoKey)
    {
        if (Units::exists($bepadoKey)) {
            $this->_setFieldData(self::DATABASE_BASE_STRING.'bepadounitkey', $bepadoKey);
        }

        return $this;
    }

    /**
     * Based on a given key in oxid this method will try to create a
     * bepado key value.
     *
     * @param string $oxidUnitKey
     *
     * @return string
     */
    public function guessBepadoKey($oxidUnitKey)
    {
        return isset($this->oxidUnitMapper[$oxidUnitKey])
            ? $this->oxidUnitMapper[$oxidUnitKey]
            : self::DEFAULT_BEPADO_UNIT_KEY;
    }
}
