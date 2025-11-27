<?php

namespace FYS\App\Controllers\Auth;

use FYS\App\Models\User;
use FYS\Helpers\Error;
use FYS\App\Controllers\QrController;

use FYS\App\Controllers\Mail\NotificationController;

use FYS\App\Models\Token;

class EmailEvents {

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


    public function resendToken() {
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
        $nombre = $usuario['nombre'];
        $result = $this->token->invalidateAllTokensForUser($id, 'email_verify');
       
        $newtoken = $this->user->generarCodigoAlfanumerico(12);
        $insertNewToken = $this->token->insertToken($id, $newtoken, 'email_verify');
        if ( $insertNewToken instanceof Error ){
            return [
                'success' => false,
                'message' => $insertNewToken->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        $emailSent = false;
        $emailError = null;
        try {
            $genQRCode = $this->qrController->genQRCode($newtoken, $id, $correo);
            $emailSent = $this->notificationController->enviarBienvenidaQR( $correo, $nombre, $newtoken, $genQRCode );

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
            'codigo_acceso' => $newtoken,
            'email_sent'    => $emailSent,
            'email_error'   => $emailError,
            'fecha'         => date(FYS_FORMAT_DATE)
        ];
    }


    public function verifyEmail(){
        $correo = $_POST['correo'] ?? '';
        $token = $_POST['codigo'] ?? '';

        // Validaciones
        if (empty($correo) || empty($token)) {
            return [
                'success' => false,
                'message' => 'El token y correo es requerido',
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
        if( isset($usuario['email_verified_at']) && $usuario['email_verified_at'] !== null ) {
            return [
                'success' => true,
                'message' => 'Tu correo ya esta validado',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        $tokenData  = $this->token->validateToken($token, 'email_verify');
        if($tokenData instanceof Error) {
            return [
                'success' => false,
                'message' => $tokenData->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        $usuarioId = $tokenData['usuario_id'];
        $updateUser = $this->user->updateUser($usuarioId, null, null, null, date(FYS_FORMAT_DATE));
        if($tokenData instanceof Error) {
            return [
                'success' => false,
                'message' => $updateUser->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        // Deactivacion de token
        $deactivateToken = $this->token->deactivateToken($token);
        if($deactivateToken instanceof Error) {
            return [
                'success' => false,
                'message' => $deactivateToken->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }


        return[
            'status'    => true,
            'resultado' => 'Correo validado correctamente',
            'fecha'      => date(FYS_FORMAT_DATE)
        ];

    }


}
