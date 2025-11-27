<?php
/**
 * Init back end
 */

// Definición de constantes
define('FYS_URL', '/index.php');
define('FYS_DIR', __DIR__);
define('FYS_FORMAT_DATE', 'Y-m-d H:i:s');

function sendErrorResponse($message, $code = 500) {
    // Forzamos CORS aquí por si el handler no llegó a ejecutarse
    header('Access-Control-Allow-Origin: http://localhost:4321');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['error' => true, 'message' => $message]);
    exit;
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    sendErrorResponse('Composer autoload file not found.', 500);
}

require_once __DIR__ . '/vendor/autoload.php';

use FYS\Core\Init;
use DI\Container;
use DI\ContainerBuilder;
use FYS\Core\EnvManager;

// Cargar variables de entorno (asegúrate de que EnvManager no falle silenciosamente)
try {
    define('FYS_BACKEND_ENV', EnvManager::get('BACKEND_ENV', 'development'));
    define('FYS_BACKEND_PORT', EnvManager::get('BACKEND_PORT', '8081'));
    define('FYS_PUBLIC_FRONTEND_URL', EnvManager::get('PUBLIC_FRONTEND_URL', 'http://localhost:4321'));
} catch (Exception $e) {
    sendErrorResponse('Error loading Environment: ' . $e->getMessage());
}

function getContainer(): Container {
    global $container;
    if (null === $container) {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->useAttributes(true);
        $container = $builder->build();
    }
    return $container;
}

function init(): void {
    try {
        // 1. Instanciar contenedor
        $container = getContainer();
        /** @var Init $app */
        $app = $container->get(Init::class);
        $app->run();

    } catch (Exception $e) {
        sendErrorResponse($e->getMessage(), 500);
    }
}

init();
