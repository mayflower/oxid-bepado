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

use Bepado\SDK\Struct\Product;

/**
 * Converter for delivery values.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfProductDeliveryConverter extends mfAbstractConverter implements mfConverterInterface
{
    /**
     * Value for the delivery unit week.
     */
    const OXID_DELIVERY_UNIT_WEEK = 'WEEK';

    /**
     * Value for the delivery unit week.
     */
    const OXID_DELIVERY_UNIT_MONTH = 'MONTH';

    /**
     * {@inheritDoc}
     *
     * @param oxArticle $shopObject
     * @param Product $bepadoObject
     */
    public function fromShopToBepado($shopObject, $bepadoObject)
    {
        // deliveryDate
        $deliveryDate = DateTime::createFromFormat('Y-m-j H:i:s', $shopObject->getFieldData('oxdelivery').' 00:00:00');
        $deliveryDateTimestamp = $deliveryDate->getTimestamp();
        if ($deliveryDateTimestamp > microtime(true)) {
            $bepadoObject->deliveryDate = $deliveryDateTimestamp;
        }

        // deliveryWorkDays
        $maxDeliveryTime = (int) $shopObject->getFieldData('oxmaxdeltime');
        $deliveryUnit = $shopObject->getFieldData('oxdeltimeunit');

        switch ($deliveryUnit) {
            case self::OXID_DELIVERY_UNIT_MONTH:
                $deliveryUnit = 20;
                break;
            case self::OXID_DELIVERY_UNIT_WEEK:
                $deliveryUnit = 5;
                break;
            default:
                $deliveryUnit = 1;
        }
        $bepadoObject->deliveryWorkDays = $maxDeliveryTime * $deliveryUnit;
    }

    /**
     * {@inheritDoc}
     *
     * @param Product $bepadoObject
     * @param oxArticle $shopObject
     */
    public function fromBepadoToShop($bepadoObject, $shopObject)
    {
        $aParams = array();

        // deliveryDate
        if (null !== $bepadoObject->deliveryDate) {
            $aParams['oxarticles__oxdelivery'] = date('Y-m-d', $bepadoObject->deliveryDate);
        }

        // deliveryWorkDays
        if (null !== $bepadoObject->deliveryWorkDays) {
            $aParams['oxarticles__oxmaxdeltime'] = $bepadoObject->deliveryWorkDays;
            $aParams['oxarticles__oxdeltimeunit'] = 'DAY';
        }

        $shopObject->assign($aParams);
    }
}
