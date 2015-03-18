<?php

/*
 * Copyright (C) 2015  Mayflower GmbH
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

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
    /**
     * Default value for the prefix.
     *
     * Will assign values a in an oxAddress model.
     */
    const DEFAULT_ADDRESS_FIELD_PREFIX = 'oxaddress__ox';

    /**
     * Mapping between a OXID values of a objects's address data to
     * the properties of the SDK's Address model.
     *
     * @var array
     */
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
     * @param mfBepadoProduct $object
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
     * {@inheritDocs}
     *
     * In difference to the other converter this one will set the values on
     * different object like oxBasket, oxOrder or oxArticle as default. So the prefix
     * for the field values is needed or defaults to the oxArticle version.
     *
     * @param Struct\Address $object
     * @param string $type
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
     * Does the real mapping work.
     *
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

    /**
     * Will create the value of a oxState model by its id.
     *
     * @param $stateId
     * @return mixed|null
     */
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

    /**
     * Will create the country shortcut by a given country id.
     *
     * @param $countryId
     * @return mixed|null
     */
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
