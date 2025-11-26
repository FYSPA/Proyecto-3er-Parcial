<?php

namespace FYS\Core;

use FYS\Core\CorsHandler;
use FYS\Core\RoutesManager;
use FYS\Core\DatabaseManager;

class Init {
    private RoutesManager $router;

    public function __construct(RoutesManager $router) {
        $this->router = $router;
    }

    public function run() {
        $this->cors();

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $basePath = FYS_URL;
        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
            if ($uri === ''){
                $uri = '/';
            }
        }

        $this->router->dispatch($httpMethod, $uri);
    }

    private function cors() {
        $cors = new CorsHandler();
        $cors->handle();
    }
}
