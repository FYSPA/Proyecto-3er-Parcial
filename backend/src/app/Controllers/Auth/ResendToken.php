<?php

namespace FYS\App\Controllers\Auth;

use FYS\App\Models\User;
use FYS\Helpers\Error;
use FYS\App\Controllers\QrController;

use FYS\App\Controllers\Mail\NotificationController;

class ResendLogin {

    private User $user;
    private NotificationController $notificationController;
    private QrController $qrController;

    public function __construct(User $user, NotificationController $notificationController, QrController $qrController) {
        $this->user = $user;
        $this->qrController = $qrController;
        $this->notificationController = $notificationController;
    }


    public function resendLogin() {
        $correo = $_POST['correo'] ?? '';

        // Validaciones
        if (empty($correo)) {
            return [
                'success' => false,
                'message' => 'El correo es requerido',
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

        $result = $this->user->getUserByEmail($correo);
        if($result instanceof Error) {
            return [
                'success' => false,
                'message' => $result->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }
        $usuario = $result->fetch_assoc();
        $id = $usuario['id'];
        $codigo_acceso = $usuario['codigo_acceso'];
        $nombre = $usuario['nombre'];
        $emailSent = false;
        $emailError = null;

        try {
            $genQRCode = $this->qrController->genQRCode($codigo_acceso, $id);
            $emailSent = $this->notificationController->enviarBienvenidaQR($correo, $nombre, $codigo_acceso, $genQRCode);

            if (!$emailSent) {
                $emailError = "La función enviarCorreoConQR devolvió false.";
            }
        } catch (\Throwable $e) {
            $emailError = $e->getMessage();
        }

        // --- Respuesta final ---
        return [
            'success'       => true,
            'message'       => 'Reenvio exitoso',
            'codigo_acceso' => $codigo_acceso,
            'email_sent'    => $emailSent,
            'email_error'   => $emailError,
            'fecha'         => date(FYS_FORMAT_DATE)
        ];
    }


}
