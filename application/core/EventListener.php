<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class EventListener
{
    /**
     * @var VersionLayerInterface
     */
    private $_oVersionLayer;

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
                    $this->getVersionLayer()->getDb()->execute($query);
                } catch (\Exception $e) {
                    // todo log if possible
                }

            }
        }
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
 