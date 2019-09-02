<?php
class MigrateToCoreApi extends Migration
{
    public function up()
    {
        require_once __DIR__ . '/../classes/RouteMap.php';

        // Convert plugin type to core API plugin type
        $query = "UPDATE `plugins`
                  SET `plugintype` = REPLACE(`plugintype`, 'APIPlugin', 'RESTAPIPlugin')
                  WHERE `pluginclassname` = 'SchwarzesBrettAPI'
                    AND FIND_IN_SET('APIPlugin', `plugintype`) > 0";
        DBManager::get()->exec($query);

        // Activate routes
        RESTAPI\ConsumerPermissions::get('global')->activateRouteMap(new SchwarzesBrett\RouteMap());
    }

    public function down()
    {
        // Convert plugin type to Rest.IP plugin type
        $query = "UPDATE `plugins`
                  SET `plugintype` = REPLACE(`plugintype`, 'RESTAPIPlugin', 'APIPlugin')
                  WHERE `pluginclassname` = 'SchwarzesBrettAPI'
                    AND FIND_IN_SET('RESTAPIPlugin', `plugintype`) > 0";
        DBManager::get()->exec($query);
    }
}
