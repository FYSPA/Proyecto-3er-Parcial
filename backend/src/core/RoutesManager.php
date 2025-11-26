<?php

namespace FYS\Core;

use FastRoute;
use FastRoute\Dispatcher;

/**
 * Clase manejador de rutas
 *
*/

class RoutesManager {

    private Dispatcher $dispatcher;

    public function __construct() {
        $routes = require_once __DIR__ . '/../routes/web.php';
        $this->dispatcher = FastRoute\simpleDispatcher($routes);
    }

    public function dispatch(string $httpMethod, string $uri): void {
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                echo 'NOT FOUND';
                // ... 404 Not Found
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                echo 'METHOD NOT ALLOWED';
                // ... 405 Method Not Allowed
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                
                list($class, $method) = $handler;
                $controller = getContainer()->get($class);
                try {
                    $response = call_user_func_array([$controller, $method], $vars);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                } catch (\Throwable $e) {
                    http_response_code(500);
                    echo json_encode([
                        'error' => true,
                        'message' => $e->getMessage()
                    ]);
                }
            break;
            default:
                break;
        }
    }
}
