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

    // AUTH ROUTES
    $r->addRoute('POST',     '/api/auth/login',         ['FYS\App\Controllers\Auth\Login', 'login']);
    $r->addRoute('POST',     '/api/auth/registro',      ['FYS\App\Controllers\Auth\Register', 'register']);
    $r->addRoute('POST',     '/api/auth/resendlogin',   ['FYS\App\Controllers\Auth\ResendLogin', 'resendLogin']);
    // Rutas API calendario
    // $r->addRoute('GET',     '/api/events',          ['App\Controllers\EventController', 'list']);
    // $r->addRoute('POST',    '/api/events',          ['App\Controllers\EventController', 'store']);
    // $r->addRoute('PUT',     '/api/events/{id:\d+}', ['App\Controllers\EventController', 'update']);
    // $r->addRoute('DELETE',  '/api/events/{id:\d+}', ['App\Controllers\EventController', 'destroy']);
};
