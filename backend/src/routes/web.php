<?php
/**
 * Archivo de rutas
 *
 * @since 1.0.0
*/

use FastRoute\RouteCollector;

return function(RouteCollector $r){
    $r->addRoute('GET',     '/api/status',          ['FYS\App\Controllers\ApiController', 'status']);
    $r->addRoute('GET',     '/api/dbstatus',        ['FYS\App\Controllers\ApiController', 'dbStatus']);
    $r->addRoute('POST',     '/api/auth/login',     ['FYS\App\Controllers\Auht\Login', 'login']);
    // Rutas API calendario
    // $r->addRoute('GET',     '/api/events',          ['App\Controllers\EventController', 'list']);
    // $r->addRoute('POST',    '/api/events',          ['App\Controllers\EventController', 'store']);
    // $r->addRoute('PUT',     '/api/events/{id:\d+}', ['App\Controllers\EventController', 'update']);
    // $r->addRoute('DELETE',  '/api/events/{id:\d+}', ['App\Controllers\EventController', 'destroy']);
};
