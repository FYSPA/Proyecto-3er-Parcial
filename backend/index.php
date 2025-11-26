<?php

define('FYS_URL', '/index.php');
define('FYS_DIR', __DIR__);

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo '<div class="error"><p><strong>Error:</strong> Composer autoload file not found. Please run <code>composer install</code> in the project directory.</p></div>';
    return;
}

require_once __DIR__ . '/vendor/autoload.php';

use FYS\Core\Init;
use DI\Container;
use DI\ContainerBuilder;

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
