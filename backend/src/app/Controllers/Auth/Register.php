<?php

namespace FYS\App\Controllers\Auth;

use FYS\App\Models\User;
use FYS\App\Models\Token;
use FYS\Helpers\Error;

use FYS\App\Controllers\Mail\NotificationController;
use FYS\App\Controllers\QrController;

class Register {

    private User $user;
    private Token $token;
    private NotificationController $notificationController;
    private QrController $qrController;

    public function __construct(User $user, Token $token, NotificationController $notificationController, QrController $qrController) {
        $this->user = $user;
        $this->token = $token;
        $this->qrController = $qrController;
        $this->notificationController = $notificationController;
    }


    public function register() {
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $password = $_POST['password'] ?? '';

        // 1. Validaciones
        if (empty($nombre) || empty($correo) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Campos requeridos: nombre, correo, password',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Email inválido',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        // 2. Hash de password
        $password_hashed = password_hash($password, PASSWORD_BCRYPT);

        // 3. Insertar usuario
        try {
            $stmt = $this->user->insertUser($correo, $password_hashed, $nombre);
            
            if ($stmt instanceof Error) {
                return [
                    'success' => false,
                    'message' => $stmt->getMessage(),
                    'fecha'   => date(FYS_FORMAT_DATE)
                ];
            }

            if(!is_array($stmt) || !isset($stmt['user']) || empty($stmt['user']['id'])) {
                return [
                    'success' => false,
                    'message' => 'Error interno: respuesta inválida del modelo',
                    'fecha'   => date(FYS_FORMAT_DATE)
                ];
            }

            // Obtener el ID del nuevo usuario
            $newId = $stmt['user']['id'];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error interno al crear usuario: ' . $e->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        // 4. Generar Token y Guardar
        $codigo_acceso = $this->user->generarCodigoAlfanumerico(12);

        try {
            $tokenResult = $this->token->insertToken($newId, $codigo_acceso);

            if ($tokenResult instanceof Error) {
                $deleteUser = $this->user->deleteUser($newId);
                
                $msgError = "Error al crear token: " . $tokenResult->getMessage();
                if ($deleteUser instanceof Error) {
                    $msgError .= " | ADEMÁS falló el rollback del usuario: " . $deleteUser->getMessage();
                }

                return [
                    'success' => false,
                    'message' => $msgError,
                    'fecha'   => date(FYS_FORMAT_DATE)
                ];
            }

        } catch (\Throwable $e) {
            // Rollback también en el catch
            $this->user->deleteUser($newId);
            return [
                'success' => false,
                'message' => 'Excepción al guardar token: ' . $e->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        // 5. Generar QR y Enviar Correo
        $emailSent = false;
        $emailError = null;

        try {
            // Generar imagen física
            $rutaQr = $this->qrController->genQRCode($codigo_acceso, $newId);
            
            // Enviar correo
            $emailSent = $this->notificationController->enviarBienvenidaQR(
                $correo,
                $nombre,
                $codigo_acceso,
                $rutaQr
            );

            if (!$emailSent) {
                $emailError = "El servicio de correo devolvió false sin lanzar excepción.";
            }

        } catch (\Throwable $e) {
            $emailError = $e->getMessage();
        }

        // 6. Respuesta final
        return [
            'success'       => true,
            'message'       => 'Registro exitoso',
            'user_id'       => $newId,
            'codigo_acceso' => $codigo_acceso,
            'email_sent'    => $emailSent,
            'email_error'   => $emailError,
            'fecha'         => date(FYS_FORMAT_DATE)
        ];
    }

}
