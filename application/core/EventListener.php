<?php
/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class EventListener
{
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
                    oxDb::getDb()->execute($query);
                } catch (\Exception $e) {
                    // todo log if possible
                }

            }
        }
    }
}
 