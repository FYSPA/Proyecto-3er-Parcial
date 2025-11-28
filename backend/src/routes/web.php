<?php
/**
 * Archivo de rutas
 *
 * @since 1.0.0
*/

use FastRoute\RouteCollector;

return function(RouteCollector $r){
    $r->addRoute('GET',     '/status',          ['FYS\App\Controllers\ApiController', 'status']);
    $r->addRoute('GET',     '/dbstatus',        ['FYS\App\Controllers\ApiController', 'dbStatus']);

    // AUTH ROUTES
    $r->addRoute('POST',     '/auth/login',         ['FYS\App\Controllers\Auth\Login', 'login']);
    $r->addRoute('POST',     '/auth/registro',      ['FYS\App\Controllers\Auth\Register', 'register']);
    $r->addRoute('POST',     '/auth/resendtoken',   ['FYS\App\Controllers\Auth\EmailEvents', 'resendToken']);
    $r->addRoute('POST',     '/auth/verifyemail',   ['FYS\App\Controllers\Auth\EmailEvents', 'verifyEmail']);
    $r->addRoute('POST',     '/auth/logingoogle',   ['FYS\App\Controllers\Auth\Login', 'loginGoogle']);

};
