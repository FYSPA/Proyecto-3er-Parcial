<?php
// exchange-code.php (en backend/app/)
header('Content-Type: application/json; charset=utf-8');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if ($origin) {
    if ($origin === 'http://localhost:4321' || $origin === 'http://localhost:3000') {
        header('Access-Control-Allow-Origin: ' . $origin);
    } else {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
} else {
    header('Access-Control-Allow-Origin: http://localhost:4321');
}
header('Vary: Origin');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Manejo de errores
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error PHP: ' . $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit;
});

set_exception_handler(function($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Excepción: ' . $exception->getMessage()
    ]);
    exit;
});

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if ($origin) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
    header('Access-Control-Max-Age: 86400');
    http_response_code(204);
    exit;
}

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    // Desde backend/app/ subimos 3 niveles a raíz (Proyecto-3er-Parcial/)
    $envPath = __DIR__ . '/../../.env';
    if (!file_exists($envPath)) {
        throw new Exception("Archivo .env NO existe en: $envPath");
    }

    // Cargar variables de .env desde la raíz
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();

    // ========== IMPORTAR DB ==========
    require_once __DIR__ . '/../config/db.php';

    // Recibir y validar el code de Google
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $code = null;
    if (is_array($data) && isset($data['code'])) {
        $code = $data['code'];
    } else if (isset($_POST['code'])) {
        $code = $_POST['code'];
    }
    if (!$code) {
        throw new Exception('Code no recibido');
    }
    $client_id = $_ENV['GOOGLE_CLIENT_ID'] ?? null;
    $client_secret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? null;
    
    if (!$client_id || !$client_secret) {
        throw new Exception('Credenciales de Google no configuradas en .env');
    }

    // ========== INTERCAMBIAR CODE POR TOKEN ==========
    $postData = http_build_query([
        'code' => $code,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
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
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        throw new Exception('Error intercambiando code con Google: ' . ($curlError ?: 'HTTP ' . $httpCode));
    }

    $tokenData = json_decode($response, true);
    
    if (!isset($tokenData['access_token'])) {
        throw new Exception('Google no devolvió access_token: ' . ($tokenData['error_description'] ?? json_encode($tokenData)));
    }

    // ========== OBTENER INFO DEL USUARIO ==========
    $access_token = $tokenData['access_token'];
    $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . urlencode($access_token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $userinfo_response = curl_exec($ch);
    curl_close($ch);

    $userInfo = json_decode($userinfo_response, true);
    
    if (!isset($userInfo['email'])) {
        throw new Exception('No se pudo obtener información del usuario de Google');
    }

    $email = $userInfo['email'];
    $nombre = $userInfo['name'] ?? 'Usuario';
    $photo = $userInfo['picture'] ?? null;

    // ========== VERIFICAR SI USUARIO EXISTE ==========
    $stmt = $conn->prepare('SELECT id, nombre, correo, foto_url FROM usuarios WHERE correo = ?');
    if (!$stmt) {
        throw new Exception('Error preparando consulta: ' . $conn->error);
    }
    
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuario ya existe
        $user = $result->fetch_assoc();
        $stmt->close();
        
        // Lógica para actualizar la foto si es necesario (ej: si tiene avatar por defecto pero ahora entra con Google)
        // O si queremos asegurar que tenga la foto de Google guardada localmente
        $fotoActual = $user['foto_url'];
        $nuevaFoto = $fotoActual;

        // Si la foto actual es de UI Avatars (default) y Google nos da una foto, la descargamos
        // O si queremos forzar actualización de foto de Google
        if ($photo && (strpos($fotoActual, 'ui-avatars.com') !== false || strpos($fotoActual, 'googleusercontent.com') !== false)) {
             $avatarFilename = $user['id'] . '.jpg';
             // Ruta absoluta del sistema de archivos para guardar (en app/uploads)
             $avatarPath = __DIR__ . '/uploads/avatars/' . $avatarFilename;
             // URL pública para guardar en BD (relativa)
             $avatarUrl = '/uploads/avatars/' . $avatarFilename; 
             
             if (!is_dir(__DIR__ . '/uploads/avatars/')) {
                 mkdir(__DIR__ . '/uploads/avatars/', 0755, true);
             }

             $ch = curl_init($photo);
             $fp = fopen($avatarPath, 'wb');
             if ($fp) {
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $success = curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                if ($success) {
                    $nuevaFoto = $avatarUrl;
                    // Actualizar en BD
                    $update = $conn->prepare("UPDATE usuarios SET foto_url = ? WHERE id = ?");
                    $update->bind_param("ss", $nuevaFoto, $user['id']);
                    $update->execute();
                    $update->close();
                }
             }
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user_id' => $user['id'],
            'user_nombre' => $user['nombre'],
            'user_correo' => $user['correo'],
            'user_photo' => $nuevaFoto,
            'mensaje' => 'Usuario encontrado'
        ]);
        exit;
    }

    // ========== CREAR NUEVO USUARIO ==========
    $stmt->close();
    
    $id = 'user_' . uniqid();
    $codigo_acceso = bin2hex(random_bytes(8));
    $pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);

    // Imagen por defecto
    $fotoFinal = 'https://ui-avatars.com/api/?name=' . urlencode($nombre) . '&background=4CAF50&color=fff';
    
    if ($photo) {
        $avatarFilename = $id . '.jpg';
        $avatarPath = __DIR__ . '/uploads/avatars/' . $avatarFilename;
        // Usamos URL relativa
        $avatarUrl = '/uploads/avatars/' . $avatarFilename;
        
        // Crear directorio si no existe
        if (!is_dir(__DIR__ . '/uploads/avatars/')) {
            mkdir(__DIR__ . '/uploads/avatars/', 0755, true);
        }
        
        // Intentar descargar imagen
        $ch = curl_init($photo);
        $fp = fopen($avatarPath, 'wb');
        if ($fp) {
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $success = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            
            if ($success) {
                $fotoFinal = $avatarUrl;
            }
        }
    }

    // ========== INSERTAR USUARIO ==========
    $insert = $conn->prepare('INSERT INTO usuarios (id, nombre, correo, password, verificado, codigo_acceso, foto_url) VALUES (?, ?, ?, ?, 1, ?, ?)');
    if (!$insert) {
        throw new Exception('Error preparando insert: ' . $conn->error);
    }
    
    $insert->bind_param('ssssss', $id, $nombre, $email, $pass, $codigo_acceso, $fotoFinal);
    
    if (!$insert->execute()) {
        if ($insert->errno === 1062) {
            throw new Exception('Ya existe una cuenta con este correo.');
        } else {
            throw new Exception('Error creando usuario: ' . $insert->error);
        }
    }
    
    $insert->close();

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'user_id' => $id,
        'user_nombre' => $nombre,
        'user_correo' => $email,
        'user_photo' => $fotoFinal,
        'mensaje' => 'Usuario creado exitosamente'
    ]);
    
    $conn->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
