<?php

namespace FYS\App\Models;

use DateTime;
use FYS\Core\DatabaseManager;
use FYS\Helpers\Error;

class Token extends DatabaseManager {

    private string $tablename ='Tokens';

    public function __construct() {
        return parent::__construct();
    }

    public function insertToken(string $usuario_id, string $token, string $tipo = 'email_verify'): array|Error {
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $stmt = $conn->prepare(
            "INSERT INTO $this->tablename (usuario_id, token, tipo, expira_en)
            VALUES (?, ?, ?, ?)"
        );

        if (!$stmt) {
            return new Error('Error interno al preparar consulta de token', 500);
        }

        // 1. Calcular Expiración
        $fecha = new \DateTime();
        if ($tipo === 'qr_login') {
            $fecha->modify('+15 minutes');
        } else {
            $fecha->modify('+2 hours');
        }
        
        $expira_en = $fecha->format(FYS_FORMAT_DATE);
        $stmt->bind_param("ssss", $usuario_id, $token, $tipo, $expira_en);

        if (!$stmt->execute()) {
            return new Error(
                'Error al guardar el token: ' . $stmt->error,
                500,
                [$stmt]
            );
        }

        return [
            'success' => true,
            'token_data' => [
                'id' => $stmt->insert_id, // ID autoincremental de la tabla tokens
                'usuario_id' => $usuario_id,
                'token' => $token,
                'tipo' => $tipo,
                'expira_en' => $expira_en
            ]
        ];
    }

    public function checkTokenActive (string $correo){
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $stmt = $conn->prepare(
            "SELECT 1 FROM $this->tablename t INNER JOIN users u ON t.usuario_id = u.id"
        );

        if (!$stmt) {
            return new Error('Error interno al preparar consulta de token', 500);
        }


    }
}
