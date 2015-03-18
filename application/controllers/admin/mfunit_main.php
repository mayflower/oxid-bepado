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

/**
 * This controller will render the main view of the unit configuration/mapping admin.
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class mfunit_main extends oxAdminDetails
{
    /**
     * @var VersionLayerInterface
     */
    protected $_oVersionLayer;

    /**
     * All Available bepado unit keys.
     *
     * @var array
     */
    protected $allBepadoKeys = array(
        'kg' => 'kg',
        'g' => 'g',
        'l' => 'l',
        'ml' => 'ml',
        'cm' => 'cm',
        'mm' => 'mm',
        'm' => 'm',
        'm^2' => 'm^2',
        'm^3' => 'm^3',
        'piece' => 'piece',
    );

    public function render()
    {
        parent::render();

        /** @var mfBepadoUnit $oBepadoUnit */
        $oBepadoUnit = $this->getVersionLayer()->createNewObject('mfBepadoUnit');
        $this->_aViewData['edit'] = $oBepadoUnit;

        $oxId = $this->getEditObjectId();
        if ($oxId && '-1' !== $oxId) {
            $oBepadoUnit->load($oxId);
        }

        return 'mfunit_main.tpl';
    }

    /**
     * Persists the chosen mapping.
     */
    public function save()
    {
        parent::save();

        $aParams = $this->getVersionLayer()->getConfig()->getRequestParameter("editval");

        $oBepadoConfiguration = $this->getVersionLayer()->createNewObject('mfBepadoUnit');
        $oBepadoConfiguration->load($this->getEditObjectId());
        $oBepadoConfiguration->assign($aParams);
        $oBepadoConfiguration->save();

        // set oxid if inserted
        $this->setEditObjectId($oBepadoConfiguration->getId());
    }

    /**
     * To avoid double mapping, this method will create a list of available bebado
     * units including the own when set and and wanted.
     *
     * @param mfBepadoUnit $bepadoUnit
     *
     * @return array
     */
    public function computeAvailableBepadoUnits(mfBepadoUnit $bepadoUnit)
    {
        $result = $db = $this->getVersionLayer()->getDb()->getAll('SELECT BEPADOUNITKEY FROM mfbepadounits');
        $mappedKeys = array();
        foreach ($result as $row) {
            $mappedKeys[] = array_shift($row);
        }
        $mappedKeys = array_flip($mappedKeys);

        $diffKeys = array_diff_key($this->allBepadoKeys, $mappedKeys);

        $currentBepadoKey = $bepadoUnit->getBepadoKey();
        if (null === $currentBepadoKey) {
            return $diffKeys;
        }

        $diffKeys[$currentBepadoKey] = isset($allKeys[$currentBepadoKey]) ? $allKeys[$currentBepadoKey] : $currentBepadoKey;

        return $diffKeys;
    }

    /**
     * To avoid double mapping, this method will create a list of available oxid
     * units including the own when set and and wanted.
     *
     * @param mfBepadoUnit $bepadoUnit
     *
     * @return array
     */
    public function computeAvailableOxidUnits(mfBepadoUnit $bepadoUnit)
    {
        $result = $db = $this->getVersionLayer()->getDb()->getAll('SELECT OXID FROM mfbepadounits');
        $mappedKeys = array();
        foreach ($result as $row) {
            $mappedKeys[] = array_shift($row);
        }
        $mappedKeys = array_flip($mappedKeys);
        $allKeys = $this->getVersionLayer()->getLang()->getSimilarByKey("_UNIT_", null, false);
        $diffKeys = array_diff_key($allKeys, $mappedKeys);

        $currentOxidKey = $bepadoUnit->getId();
        if (null === $currentOxidKey) {
            return $diffKeys;
        }

        $diffKeys[$currentOxidKey] = isset($allKeys[$currentOxidKey]) ? $allKeys[$currentOxidKey] : $currentOxidKey;

        return $diffKeys;
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

    /**
     * @param VersionLayerInterface $versionLayer
     */
    public function setVersionLayer(VersionLayerInterface $versionLayer)
    {
        $this->_oVersionLayer = $versionLayer;
    }
}
