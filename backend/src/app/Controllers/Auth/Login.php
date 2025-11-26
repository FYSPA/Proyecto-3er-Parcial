<?php

namespace FYS\App\Controllers\Auth;

use FYS\App\Models\User;
use FYS\Helpers\Error;

class Login {

    private User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }


    public function login() {
        $correo = $_POST['correo'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($correo) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email y contraseña requeridos',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        // Preparar statement
        $result = $this->user->getUserByEmail($correo);

        if($result instanceof Error){
            return [
                'success' => false,
                'message' => $result->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        $usuario = $result->fetch_assoc();

        if( !isset($usuario['email_verified_at']) || empty($usuario['email_verified_at']) ) {
            return [
                'success' => false,
                'message' => 'Su correo no esta verificado',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        if (password_verify($password, $usuario['password'])) {
            return [
                'success'      => true,
                'user_id'      => $usuario['id'],
                'user_nombre'  => $usuario['nombre'],
                'user_correo'  => $usuario['correo'],
                'fecha'        => date(FYS_FORMAT_DATE)
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Contraseña incorrecta',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }
    }
}
