<?php

use Bepado\SDK\Struct as Struct;

/**
 * To convert address data from oxid to bepado and back.
 *
 * The type is a special converter behavior, cause when converting into oxid addresses we will mostly
 * not convert into a oxAddress object. We will create an array with common oxid field names as keys
 * to assign them.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class mf_sdk_address_converter extends mf_abstract_converter implements mf_converter_interface
{
    const DEFAULT_ADDRESS_FIELD_PREFIX = 'oxaddress__ox';

    private $sdkAddressMapper = array(
        '%typecompany'   => 'company',
        '%typefname'     => 'firstName',
        '%typelname'     => 'surName',
        '%typestreet'    => 'street',
        '%typestreetnr'  => 'streetNumber',
        '%typeaddinfo'   => 'additionalAddressLine',
        '%typecity'      => 'city',
        '%typezip'       => 'zip',
        '%typefon'       => 'phone',
        '%typeemail'     => 'email',
        '%typecountryid' => 'country',
        '%typestateid'   => 'state',
    );

    /**
     * {@inheritDoc}
     *
     * This method will create an address object only, when the type is not set or equals the
     * prefix for the oxAddress field names. So we will create an array for assigning the
     * values first.
     *
     * @param oxBase $object
     *
     * @return Struct\Address|array
     */
    public function fromShopToBepado($object, $type = 'oxaddress__ox')
    {
        $sdkAddress = new Struct\Address();

        foreach ($this->sdkAddressMapper as $rawFieldName => $property) {
            $fieldName = str_replace('%type', $type, $rawFieldName);
            $fieldValue = $object->getFieldData($fieldName);
            if (null !== $fieldValue) {
                if (strpos($fieldName, 'stateid')) {
                    $fieldValue = $this->createState($fieldValue);
                } elseif (strpos($fieldName, 'countryid')) {
                    $fieldValue = $this->createCountry($fieldValue);
                }
                $sdkAddress->$property = $fieldValue;
            }
        }

        // when converting from oxUser the mail isn't in __oxmail
        if (null === $sdkAddress->email && null != $object->getFieldData('oxuser__oxusername')) {
            $sdkAddress->email = $object->getFieldData('oxuser__oxusername');
        }

        return $sdkAddress;
    }

    /**
     * @param Struct\Address $object
     *
     * @return array|oxAddress
     */
    public function fromBepadoToShop($object, $type = 'oxaddress__ox')
    {
        $fieldData = $this->createFieldDataFromAddress($object, $type);

        if ($type === self::DEFAULT_ADDRESS_FIELD_PREFIX) {
            $oxAddress = $this->getVersionLayer()->createNewObject('oxaddress');
            $oxAddress->assign($fieldData);

            return $oxAddress;
        }

        return $fieldData;
    }

    /**
     * @param Struct\Address $address
     * @param string         $type
     * @return array
     */
    private function createFieldDataFromAddress(Struct\Address $address, $type)
    {
        $oxCountry = $this->getVersionLayer()->createNewObject('oxcountry');
        $select = $oxCountry->buildSelectString(array('OXISOALPHA3' => $address->country, 'OXACTIVE' => 1));
        $countryID = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oxCountryId = $countryID ?: null;

        $oxState = $this->getVersionLayer()->createNewObject('oxstate');
        $select = $oxState->buildSelectString(array('OXTITLE' => $address->state));
        $stateID = $this->getVersionLayer()->getDb(true)->getOne($select);
        $oxStateId = $stateID ?: null;

        $fieldData = array(
            $type.'company'   => $address->company,
            $type.'fname'     => $address->firstName.(
                strlen($address->middleName) > 0
                    ? ' '.$address->middleName
                    : ''
                ),
            $type.'lname'      => $address->surName,
            $type.'street'    => $address->street,
            $type.'streetnr'  => $address->streetNumber,
            $type.'addinfo'   => $address->additionalAddressLine,
            $type.'city'      => $address->city,
            $type.'countryid' => $oxCountryId,
            $type.'stateid'   => $oxStateId,
            $type.'zip'       => $address->zip,
            $type.'fon'       => $address->phone,
        );

        if (null !== $address->email) {
            $fieldData[$type.'email'] = $address->email;
        }

        return $fieldData;
    }

    private function createState($stateId)
    {
        /** @var oxState $oxState */
        $oxState = $this->getVersionLayer()->createNewObject('oxstate');
        $oxState->load($stateId);
        if ($oxState->isLoaded()) {
            return $oxState->getFieldData('oxtitle');
        }

        return null;
    }

    private function createCountry($countryId)
    {
        /** @var oxState $oxState */
        $oxState = $this->getVersionLayer()->createNewObject('oxcountry');
        $oxState->load($countryId);
        if ($oxState->isLoaded()) {
            return $oxState->getFieldData('oxisoalpha3');
        }

        return null;
    }
}
