<?php
class MigrateToCoreApi extends Migration
{
    public function up()
    {
        // Convert plugin type to core API plugin type
        $query = "UPDATE `plugins`
                  SET `plugintype` = REPLACE(`plugintype`, 'APIPlugin', 'RESTAPIPlugin')
                  WHERE `pluginclassname` = 'SchwarzesBrettAPI'
                    AND FIND_IN_SET('APIPlugin', `plugintype`) > 0";
        DBManager::get()->exec($query);

        // Activate routes
        $permissions = RESTAPI\ConsumerPermissions::get('global');
        $permissions->set('/schwarzes-brett/categories', 'get', true, true);
        $permissions->set('/schwarzes-brett/categories/:id', 'get', true, true);
        $permissions->set('/schwarzes-brett/categories/:id/articles', 'get', true, true);

        $permissions->set('/schwarzes-brett/articles', 'post', true, true);
        $permissions->set('/schwarzes-brett/article/:id', 'get', true, true);
        $permissions->set('/schwarzes-brett/article/:id', 'patch', true, true);
        $permissions->set('/schwarzes-brett/article/:id', 'delete', true, true);

        $permissions->set('/schwarzes-brett/article/:id/visit', 'post', true, true);

        $permissions->store();
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
