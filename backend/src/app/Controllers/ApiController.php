<?php

namespace FYS\App\Controllers;

use FYS\Core\DatabaseManager;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class ApiController {

    private DatabaseManager $db;

    public function __construct(DatabaseManager $db) {
        $this->db = $db;
    }
    /**
     *
    */
    public function status(?Request $request = null, ?Response $response = null) {
        return [
            'status' => true,
            'fecha'  => date('Y-m-d H:i:s'),
            'mensaje' => 'API funcionando correctamente'
        ];
    }

    public function dbStatus(?Request $request = null, ?Response $response = null) {
        header('Content-Type: application/json; charset=utf-8');

        $conn = $this->db->getConnection();

        // Si retorna un objeto Error
        if ($conn instanceof \FYS\Helpers\Error) {
            return [
                'status'  => false,
                'message' => $conn->getMessage(),
                'fecha'   => date('Y-m-d H:i:s')
            ];
        }

        // Si sí conecta
        return [
            'status'  => true,
            'message' => 'Conexión exitosa',
            'fecha'   => date('Y-m-d H:i:s')
        ];
    }
}
