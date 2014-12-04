<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class EventListener
{
    /**
     * @var VersionLayerInterface
     */
    public static $_oVersionLayer;

    public function onActivate()
    {
        $schemaDir = __DIR__ . '/../install';
        $sqlFiles = array_filter(
            scandir($schemaDir),
            function ($file) { return substr($file, -4) === '.sql'; }
        );

        sort($sqlFiles);
        foreach ($sqlFiles as $sqlFile) {
            $sql = file_get_contents($schemaDir . '/' . $sqlFile);
            $sql = str_replace("\n", "", $sql);
            $queries = explode(';', $sql);

            foreach ($queries as $query) {
                if (empty($query)) {
                    continue;
                }
                try {
                    self::getVersionLayer()->getDb()->execute($query);
                } catch (\Exception $e) {
                    // todo log if possible
                }

            }
        }

        /** @var oxGroups $oxUserGruop */
        $oxUserGruop = self::getVersionLayer()->createNewObject('oxgroups');
        $oxUserGruop->load('bepadoshopgroup');
        if (!$oxUserGruop->isLoaded()) {
            throw new \Exception('No bepado user group found.');
        }

        /** @var oxDelivery $oxDelivery */
        $oxDelivery = self::getVersionLayer()->createNewObject('oxdelivery');
        $oxDelivery->load('bepadoshippingrule');
        if (!$oxDelivery->isLoaded()) {
            throw new \Exception('No bepado shipping found');
        }

        /** @var oxDeliveryset $oxDeliverySet */
        $oxDeliverySet = self::getVersionLayer()->createNewObject('oxdeliveryset');
        $oxDeliverySet->load('bepadoshipping');
        if (!$oxDeliverySet->isLoaded()) {
            throw new \Exception('No bepado shipping rule found');
        }

        $oObject2Delivery = self::getVersionLayer()->createNewObject('oxbase');
        $oObject2Delivery->init('oxobject2delivery');
        $oObject2Delivery->oxobject2delivery__oxdeliveryid = new oxField('bepadoshipping');
        $oObject2Delivery->oxobject2delivery__oxobjectid = new oxField('bepadoshopgroup');
        $oObject2Delivery->oxobject2delivery__oxtype = new oxField("oxdelsetg");
        $oObject2Delivery->save();

        $oObject2Delivery = self::getVersionLayer()->createNewObject('oxbase');
        $oObject2Delivery->init('oxobject2delivery');
        $oObject2Delivery->oxobject2delivery__oxdeliveryid = new oxField('bepadoshippingrule');
        $oObject2Delivery->oxobject2delivery__oxobjectid = new oxField('bepadoshipping');
        $oObject2Delivery->oxobject2delivery__oxtype = new oxField("oxdelset");
        $oObject2Delivery->save();
    }

    /**
     * Create and/or returns the VersionLayer.
     *
     * @return VersionLayerInterface
     */
    private function getVersionLayer()
    {
        /** @var VersionLayerFactory $factory */
        $factory = oxNew('VersionLayerFactory');
        $oVersionLayer = $factory->create();

        return $oVersionLayer;
    }
}
 