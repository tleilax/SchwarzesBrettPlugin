<?php
require_once __DIR__ . '/bootstrap.inc.php';

/**
 * @todo test and extend
 */
class SchwarzesBrettAPI extends StudIPPlugin implements RESTAPIPlugin
{
    public function getRouteMaps()
    {
        return [
            new SchwarzesBrett\RouteMap(),
        ];
    }
}
