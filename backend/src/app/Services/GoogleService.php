<?php
namespace FYS\App\Services;

use FYS\Core\EnvManager;

class GoogleService {
    private $clientId;
    private $clientSecret;

    public function __construct() {
        $this->clientId = EnvManager::get('GOOGLE_CLIENT_ID');
        $this->clientSecret = EnvManager::get('GOOGLE_CLIENT_SECRET');

        if (!$this->clientId || !$this->clientSecret) {
            throw new \Exception('Credenciales de Google no configuradas en .env');
        }
    }

    public function getAccessToken($code) {
        $postData = http_build_query([
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => 'postmessage',
            'grant_type' => 'authorization_code'
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            throw new \Exception('Error al conectar con Google Token API');
        }

        return json_decode($response, true);
    }

    public function getUserInfo($accessToken) {
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($accessToken));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        curl_close($ch);

        $userInfo = json_decode($response, true);
        if (!isset($userInfo['email'])) {
            throw new \Exception('No se pudo obtener el perfil del usuario de Google');
        }

        return $userInfo;
    }
}
