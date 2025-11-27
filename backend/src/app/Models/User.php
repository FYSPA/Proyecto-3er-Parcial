<?php

namespace FYS\App\Models;

use FYS\Core\DatabaseManager;
use FYS\Helpers\Error;
use mysqli_result;

class User extends DatabaseManager {

    public function getUserByEmail(string $correo): mysqli_result|Error {
        $conn = $this->getConnection();

        // Error en conexión
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $stmt = $conn->prepare("SELECT id, nombre, correo, password, email_verified_at, created_at, updated_at FROM usuarios WHERE correo = ?");
        if (!$stmt) {
            return new Error(
                'Error interno al preparar consulta',
                400
            );
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        if(!$stmt) {
            return new Error('Usuario no encontrado', 400);
        }
        return $stmt->get_result();
    }
    
    public function insertUser(string $correo, string $password_hashed, string $nombre): array|Error {
        $conn = $this->getConnection();

        // Error en conexión
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        $id_usuario = uniqid("user_");

        $stmt = $conn->prepare(
            "INSERT INTO usuarios (id, nombre, correo, password)
            VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) {
            return new Error(
                'Error interno al preparar consulta',
                400
            );
        }

        $stmt->bind_param("ssss", $id_usuario, $nombre, $correo, $password_hashed);
        if (!$stmt->execute()) {
            if ($stmt->errno == 1062) {
                return new Error(
                    'El correo electrónico ya está registrado',
                    400
                );
            }

            return new Error(
                'Error ejecutando consulta: ' . $stmt->error,
                400,
                [$stmt]
            );
        }

        return [
            'success' => true,
            'user' => [
                'id' => $id_usuario,
                'nombre' => $nombre,
                'correo' => $correo
            ]
        ];
    }

    public function deleteUser(string $id): array|Error {
        $conn = $this->getConnection();
        // Error en conexión
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        
        if (!$stmt) {
            return new Error(
                'Error interno al preparar la eliminación',
                500
            );
        }
        $stmt->bind_param("s", $id);

        if (!$stmt->execute()) {
            return new Error(
                'Error al eliminar usuario: ' . $stmt->error,
                500,
                [$stmt]
            );
        }

        if ($stmt->affected_rows === 0) {
            return new Error(
                'Usuario no encontrado o ya fue eliminado anteriormente',
                404
            );
        }

        return [
            'success' => true,
            'message' => 'Usuario eliminado correctamente',
            'id'      => $id
        ];
    }


    public function updateUser(string $id, ?string $nombre, ?string $correo, ?string $password, ?string $email_verified_at): array|Error {
        $conn = $this->getConnection();
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        // 1. Construcción dinámica de campos
        $fields = [];
        $types = "";
        $params = [];

        if ($nombre !== null) {
            $fields[] = "nombre = ?";
            $types .= "s";
            $params[] = $nombre;
        }

        if ($correo !== null) {
            $fields[] = "correo = ?";
            $types .= "s";
            $params[] = $correo;
        }

        if ($password !== null) {
            $fields[] = "password = ?";
            $types .= "s";
            $params[] = $password;
        }

        // AGREGADO: Lógica para email_verified_at
        if ($email_verified_at !== null) {
            $fields[] = "email_verified_at = ?";
            $types .= "s";
            $params[] = $email_verified_at;
        }

        if (empty($fields)) {
            return new Error('No se enviaron datos para actualizar', 400);
        }

        $sql = "UPDATE usuarios SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "s";
        $params[] = $id;

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return new Error('Error interno al preparar actualización', 500);
        }

        $stmt->bind_param($types, ...$params);

        // 4. Ejecutar
        if (!$stmt->execute()) {
            if ($stmt->errno == 1062) {
                return new Error('El correo electrónico ya está en uso', 400);
            }
            return new Error('Error al actualizar usuario: ' . $stmt->error, 500);
        }

        return [
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'id' => $id
        ];
    }

    public function generarCodigoUnico() {
        $conn = $this->getConnection();
        // Error en conexión
        if ($conn instanceof \FYS\Helpers\Error) {
            return $conn;
        }

        do {
            $codigo = $this->generarCodigoAlfanumerico(12);
            $check = $conn->prepare("SELECT id FROM usuarios WHERE codigo_acceso = ?");
            $check->bind_param("s", $codigo);
            $check->execute();
            $result = $check->get_result();
        } while ($result->num_rows > 0);
        
        return $codigo;
    }

    // Funcion util
    public function generarCodigoAlfanumerico($longitud = 12) {
        $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codigo = '';
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }

}
