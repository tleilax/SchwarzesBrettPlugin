<?php
    StudipAutoloader::addAutoloadPath(__DIR__ . '/classes', 'SchwarzesBrett\\');
    StudipAutoloader::addAutoloadPath(__DIR__ . '/models', 'SchwarzesBrett\\');

    require_once __DIR__ . '/SchwarzesBrettSearchModule.php';
