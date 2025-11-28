<?php

namespace FYS\App\Controllers\Auth;

use FYS\App\Models\User;
use FYS\App\Services\GoogleService;
use FYS\Helpers\Error;

class Login {

    private User $user;
    private GoogleService $googleService;
    private $uploadsDir;

    public function __construct(User $user, GoogleService $googleService) {
        $this->user = $user;
        $this->googleService = $googleService;
        $this->uploadsDir = FYS_DIR . '/src/uploads/avatars/';
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
        if( empty($usuario['correo']) ) {
            return [
                'success' => false,
                'code'    => 'not_user',
                'message' => 'Usuario no encontrado. Registra una cuenta para continuar.',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }
        
        if( !isset($usuario['email_verified_at']) || empty($usuario['email_verified_at']) ) {
            return [
                'success' => false,
                'code'    => 'email_not_validate',
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
                'code'    => 'incorrect_password',
                'message' => 'Contraseña incorrecta',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }
    }

    public function loginGoogle() {
        $code = $_POST['code'] ?? '';

        if(!$code) {
            return [
                'success' => false,
                'code'    => 'not_code',
                'message' => 'El codigo de Google no fue proporcionado',
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }

        $tokenData = $this->googleService->getAccessToken($code);
        $userInfo = $this->googleService->getUserInfo($tokenData['access_token']);

        $email = $userInfo['email'];
        $nombre = $userInfo['name'] ?? 'Usuario';
        $googlePhoto = $userInfo['picture'] ?? null;

        // 2. Buscar usuario en BD
        $existingUser = $this->user->getUserByEmail($email);
        if ($existingUser instanceof Error ) {
            return [
                'success' => false,
                'message' => $existingUser->getMessage(),
                'fecha'   => date(FYS_FORMAT_DATE)
            ];
        }
        $usuario = $existingUser->fetch_assoc();
        if ( $usuario ) {
            return $this->handleExistingUser($usuario, $googlePhoto);
        } else {
            return $this->handleNewUser($nombre, $email, $googlePhoto);
        }
    }

    private function handleExistingUser($user, $googlePhoto) {
        $finalPhoto = $user['foto_url'];

        // Si la foto actual es generica y Google trae una, actualizarla
        if ($googlePhoto && (strpos($finalPhoto, 'ui-avatars.com') !== false || strpos($finalPhoto, 'googleusercontent.com') !== false)) {
            $savedPath = $this->downloadImage($googlePhoto, $user['id']);
            if ($savedPath) {
                $finalPhoto = $savedPath;
                $this->user->updatePhoto($user['id'], $finalPhoto);
            }
        }

        return [
            'success' => true,
            'user_id' => $user['id'],
            'user_nombre' => $user['nombre'],
            'user_correo' => $user['correo'],
            'user_photo' => $finalPhoto,
            'mensaje' => 'Usuario encontrado'
        ];
    }

    private function handleNewUser($nombre, $email, $googlePhoto) {
        $id = 'user_' . uniqid();
        $pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        
        // Foto por defecto
        $finalPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($nombre) . '&background=4CAF50&color=fff';

        // Intentar descargar foto de Google
        if ($googlePhoto) {
            $savedPath = $this->downloadImage($googlePhoto, $id);
            if ($savedPath) {
                $finalPhoto = $savedPath;
            }
        }

        $this->user->insertUserGoogle($email, $pass, $nombre, $finalPhoto);

        return [
            'success' => true,
            'user_id' => $id,
            'user_nombre' => $nombre,
            'user_correo' => $email,
            'user_photo' => $finalPhoto,
            'mensaje' => 'Usuario creado exitosamente'
        ];
    }

    private function downloadImage($url, $userId) {
        if (!is_dir($this->uploadsDir)) {
            mkdir($this->uploadsDir, 0755, true);
        }

        $filename = $userId . '.jpg';
        $fullPath = $this->uploadsDir . $filename;
        $publicPath = '/uploads/avatars/' . $filename; // Ruta relativa para la BD

        $ch = curl_init($url);
        $fp = fopen($fullPath, 'wb');
        if ($fp) {
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            $success = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            
            return $success ? $publicPath : null;
        }
        return null;
    }
}
