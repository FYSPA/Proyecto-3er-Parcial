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
    /**
     * Valida un token.
     * @param string $token El código del token a verificar.
     * @param string $tipo El propósito del token (default: password_reset).
     * @return array|bool Retorna los datos del token (incluyendo usuario_id) si es válido, o false si no.
     */
    public function validateToken(string $token, string $tipo = 'password_reset'): array|Error {
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $sql = "SELECT id, usuario_id, token, expira_en
                FROM $this->tablename
                WHERE token = ?
                  AND tipo = ?
                  AND usado = 0
                  AND expira_en > NOW()
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return new \FYS\Helpers\Error('Error interno al validar token', 500);
        }

        $stmt->bind_param("ss", $token, $tipo);
        if (!$stmt->execute()) {
            return new \FYS\Helpers\Error('Error al ejecutar validación', 500);
        }
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if (!$data) {
            return new \FYS\Helpers\Error('Código no registrado o expirado.', 404);
        }

        return $data;
    }

    public function checkTokenActive(string $correo, string $tipo = 'password_reset') {
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $sql = "SELECT 1
                FROM $this->tablename t
                INNER JOIN usuarios u ON t.usuario_id = u.id
                WHERE u.correo = ?
                  AND t.tipo = ?
                  AND t.usado = 0
                  AND t.expira_en > NOW()
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return new \FYS\Helpers\Error('Error interno al preparar consulta de token', 500);
        }

        $stmt->execute([$correo, $tipo]);
        return $stmt->num_rows() > 0;
    }

    public function deactivateToken(string $token) {
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $sql = "UPDATE $this->tablename
                SET usado = 1
                WHERE token = ?";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return new \FYS\Helpers\Error('Error interno al desactivar el token', 500);
        }

        if ($stmt->execute([$token])) {
            return $stmt->num_rows() > 0;
        }

        return false;
    }
    
    public function invalidateAllTokensForUser(string $usuarioId, ?string $tipo) {
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $sql = "UPDATE $this->tablename SET usado = 1 WHERE usuario_id = ?";
        $params = [$usuarioId];

        // Si solo quieres invalidar los de un tipo específico (ej: solo los de reset password)
        if ($tipo) {
            $sql .= " AND tipo = ?";
            $params[] = $tipo;
        }

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            return new \FYS\Helpers\Error('Error al invalidar tokens', 500, null, 'not_invalidate_token');
        }

        return $stmt->execute($params);
    }

    /**
     * Obtiene los datos del token activo más reciente para un correo.
     */
    public function getTokenByEmail(string $correo, string $tipo = 'password_reset') {
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        // Buscamos el token, asegurando que no esté vencido ni usado
        $sql = "SELECT t.*
                FROM $this->tablename t
                INNER JOIN usuarios u ON t.usuario_id = u.id
                WHERE u.correo = ?
                  AND t.tipo = ?
                  AND t.usado = 0
                  AND t.expira_en > NOW()
                ORDER BY t.created_at DESC
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            return new \FYS\Helpers\Error('Error al preparar la búsqueda del token', 500);
        }

        $stmt->execute([$correo, $tipo]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return null;
        }

        return $result;
    }
}
