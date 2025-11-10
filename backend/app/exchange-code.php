<?php
header('Access-Control-Allow-Origin: http://localhost:4321');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../vendor/autoload.php';

$envPath = __DIR__ . '/../../.env';
if (!file_exists($envPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Archivo .env NO existe en: $envPath"]);
    exit;
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Función para descargar la imagen de Google al servidor
function descargarImagen($url, $destino) {
    $ch = curl_init($url);
    $fp = fopen($destino, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $success = curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    return $success;
}

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['code'])) {
        throw new Exception('Code no recibido');
    }

    $code = $data['code'];

    $client_id = $_ENV['GOOGLE_CLIENT_ID'] ?? null;
    $client_secret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? null;

    if (!$client_id || !$client_secret) {
        throw new Exception('Credenciales no cargadas. CLIENT_ID=' . ($client_id ?: 'NULL') . ', SECRET=' . ($client_secret ?: 'NULL'));
    }

    $postData = http_build_query([
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'redirect_uri' => 'http://localhost:4321',
        'grant_type' => 'authorization_code'
    ]);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    curl_close($ch);

    $tokenData = json_decode($response, true);

    if (!isset($tokenData['access_token'])) {
        throw new Exception('Error intercambiando code: ' . json_encode($tokenData));
    }

    $access_token = $tokenData['access_token'];

    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userinfo_response = curl_exec($ch);
    curl_close($ch);

    $userInfo = json_decode($userinfo_response, true);

    if (!isset($userInfo['email'])) {
        throw new Exception('Token inválido');
    }

    $email = $userInfo['email'];
    $nombre = $userInfo['name'] ?? 'Usuario';
    $photo = $userInfo['picture'] ?? null;

    require_once __DIR__ . '/../config/db.php';

    // Checar si usuario ya existe en BD
    $stmt = $conn->prepare('SELECT id, nombre, correo, foto_url FROM usuarios WHERE correo = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $fotoFinal = $user['foto_url'];

        echo json_encode([
            'success' => true,
            'user_id' => $user['id'],
            'user_nombre' => $user['nombre'],
            'user_correo' => $user['correo'],
            'user_photo' => $fotoFinal
        ]);
    } else {
        $id = uniqid('user_');
        $codigo_acceso = bin2hex(random_bytes(8));
        $pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

        // Descargar la imagen de Google si existe
        if ($photo) {
            $avatarFilename = $id . '.jpg';
            $avatarPath = __DIR__ . '/../uploads/avatars/' . $avatarFilename;
            $avatarUrl = '/uploads/avatars/' . $avatarFilename;

            $descargaOk = descargarImagen($photo, $avatarPath);
            $fotoFinal = $descargaOk ? $avatarUrl 
                : 'https://ui-avatars.com/api/?name=' . urlencode($nombre) . '&background=4CAF50&color=fff';
        } else {
            $fotoFinal = 'https://ui-avatars.com/api/?name=' . urlencode($nombre) . '&background=4CAF50&color=fff';
        }

        $insert = $conn->prepare('INSERT INTO usuarios (id, nombre, correo, password, verificado, codigo_acceso, foto_url) VALUES (?, ?, ?, ?, 1, ?, ?)');
        $insert->bind_param('ssssss', $id, $nombre, $email, $pass, $codigo_acceso, $fotoFinal);

        if (!$insert->execute()) {
            if ($insert->errno === 1062) {
                throw new Exception('Ya existe una cuenta con este correo.');
            } else {
                throw new Exception('Error creando usuario: ' . $insert->error);
            }
        }

        echo json_encode([
            'success' => true,
            'user_id' => $id,
            'user_nombre' => $nombre,
            'user_correo' => $email,
            'user_photo' => $fotoFinal
        ]);
    }

    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

?>
