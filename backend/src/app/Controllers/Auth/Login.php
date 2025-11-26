<?php

namespace FYS\App\Controllers;

use FYS\Core\DatabaseManager;
use Psr\Http\Message\ServerRequestInterface as Request;


class ApiController {

    private DatabaseManager $db;

    public function __construct(DatabaseManager $db) {
        $this->db = $db;
    }


    public function login(?Request $request = null) {
        header('Content-Type: application/json; charset=utf-8');

        $conn = $this->db->getConnection();

        // Error en conexión
        if ($conn instanceof \FYS\Helpers\Error) {
            return [
                'success' => false,
                'message' => $conn->getMessage(),
                'fecha'   => date('Y-m-d H:i:s')
            ];
        }

        // Obtener datos del request (PSR-7)
        $parsed = $request?->getParsedBody() ?? [];

        $correo = $parsed['correo'] ?? '';
        $password = $parsed['password'] ?? '';

        if (empty($correo) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email y contraseña requeridos',
                'fecha'   => date('Y-m-d H:i:s')
            ];
        }

        // Preparar statement
        $stmt = $conn->prepare("SELECT id, nombre, correo, password FROM usuarios WHERE correo = ?");
        if (!$stmt) {
            return [
                'success' => false,
                'message' => 'Error interno al preparar consulta',
                'fecha'   => date('Y-m-d H:i:s')
            ];
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        // Usuario encontrado
        if ($result && $result->num_rows > 0) {
            $usuario = $result->fetch_assoc();

            if (password_verify($password, $usuario['password'])) {
                return [
                    'success'      => true,
                    'user_id'      => $usuario['id'],
                    'user_nombre'  => $usuario['nombre'],
                    'user_correo'  => $usuario['correo'],
                    'fecha'        => date('Y-m-d H:i:s')
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Contraseña incorrecta',
                    'fecha'   => date('Y-m-d H:i:s')
                ];
            }
        }

        // Usuario no encontrado
        return [
            'success' => false,
            'message' => 'Usuario no encontrado',
            'fecha'   => date('Y-m-d H:i:s')
        ];
    }

}
