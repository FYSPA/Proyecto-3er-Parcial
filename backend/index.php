<?php

/**
 * Init back end
 */

// Defininción de constantes
define('FYS_URL', '/index.php');
define('FYS_DIR', __DIR__);
define('FYS_FORMAT_DATE', 'Y-m-d H:i:s');

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo '<div class="error"><p><strong>Error:</strong> Composer autoload file not found. Please run <code>composer install</code> in the project directory.</p></div>';
    return;
}

require_once __DIR__ . '/vendor/autoload.php';

use FYS\Core\Init;
use DI\Container;
use DI\ContainerBuilder;
use FYS\Core\EnvManager;

define('FYS_BACKEND_ENV', EnvManager::get('BACKEND_ENV', 'development'));
define('FYS_BACKEND_PORT', EnvManager::get('BACKEND_PORT', '8081'));
define('FYS_PUBLIC_FRONTEND_URL', EnvManager::get('PUBLIC_FRONTEND_URL', 'http://localhost:4321'));

/**
 * Función para retornar una instacia del container PHP-DI para injeccion de instancias
 *
*/
function getContainer():Container {
    global $container;

    if (null === $container) {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAttributes(true);
        
        $container = $builder->build();
    
    }
    return $container;
}

function init():void{
    try {
        $container = getContainer();
        /** @var Init $app */
        $app = $container->get(Init::class);
        $app->run();
    } catch (Exception $e) {
        echo '<div class="error"><p><strong>Error:</strong> Failed to initialize. ' . $e->getMessage() . '</p></div>';
    }
}

init();
