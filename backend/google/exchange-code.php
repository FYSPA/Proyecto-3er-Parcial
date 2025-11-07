<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

error_reporting(0);
ini_set('display_errors', 0);

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['code'])) {
        throw new Exception('Code no recibido');
    }
    
    $code = $data['code'];

    $postData = http_build_query([
        'code' => $code,
        'client_id' => getenv('GOOGLE_CLIENT_ID'),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
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
    
    if (!isset($tokenData['id_token'])) {
        throw new Exception('Error intercambiando code: ' . json_encode($tokenData));
    }

    $ch = curl_init('https://www.googleapis.com/oauth2/v1/tokeninfo?id_token=' . $tokenData['id_token']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $userInfo = json_decode($response, true);
    
    if (!isset($userInfo['email'])) {
        throw new Exception('Token inválido');
    }
    
    $email = $userInfo['email'];
    $nombre = $userInfo['name'] ?? 'Usuario';
    
    $conn = new mysqli('localhost', 'root', '', 'db_qr');
    
    $stmt = $conn->prepare('SELECT id, nombre, correo FROM usuarios WHERE correo = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode(['success' => true, 'user_id' => $user['id'], 'user_nombre' => $user['nombre'], 'user_correo' => $user['correo']]);
    }  else {
        $id = uniqid('user_');
        $codigo_acceso = bin2hex(random_bytes(8));
        $pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        
        $insert = $conn->prepare('INSERT INTO usuarios (id, nombre, correo, password, verificado, codigo_acceso) VALUES (?, ?, ?, ?, 1, ?)');
        $insert->bind_param('sssss', $id, $nombre, $email, $pass, $codigo_acceso);
        
        if (!$insert->execute()) {
            if ($insert->errno === 1062) { // Error de DUPLICATE
                throw new Exception('Ya existe una cuenta con este correo. Por favor inicia sesión.');
            } else {
                throw new Exception('Error creando usuario: ' . $insert->error);
            }
        }
        
        echo json_encode(['success' => true, 'user_id' => $id, 'user_nombre' => $nombre, 'user_correo' => $email]);
    }
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
