<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    require_once __DIR__ . '/funciones.php';
    
    require_once __DIR__ . '/funciones.php';
    require_once __DIR__ . '/../config/db.php';
    
    // La conexión $conn ya está creada en db.php

    $nombre = $_POST['nombre'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    $host_frontend = $_POST['host_frontend'] ?? 'localhost';

    if (empty($nombre) || empty($correo) || empty($password)) {
        throw new Exception('Campos requeridos');
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    $password_hashed = password_hash($password, PASSWORD_BCRYPT);
    $id_usuario = uniqid("user_");
    $codigo_acceso = generarCodigoUnico($conn);

    $stmt = $conn->prepare("INSERT INTO usuarios (id, nombre, correo, password, codigo_acceso) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $id_usuario, $nombre, $correo, $password_hashed, $codigo_acceso);

    if (!$stmt->execute()) {
        throw new Exception('DB: ' . $stmt->error);
    }

    $stmt->close();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso',
        'user_id' => $id_usuario,
        'codigo_acceso' => $codigo_acceso
    ]);
    
    ob_end_flush();
    flush();
    
    try {
        require_once __DIR__ . '/../phpqrcode/qrlib.php';
        
        $dir = __DIR__ . '/../qrcodes/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $host_para_qr = $host_frontend === 'localhost' ? gethostbyname(gethostname()) : $host_frontend;
        
        // Si es localhost, usamos puerto 8000 (backend) o 4321 (frontend) según corresponda
        // Pero para el link del QR que lleva al login_qr.php, debe apuntar al BACKEND.
        // En producción (Railway), el backend no usa puerto 8000 en la URL pública.
        
        if ($host_frontend === 'localhost' || strpos($host_frontend, '127.0.0.1') !== false) {
             $qrUrl = 'http://' . $host_para_qr . ':8000/login_qr.php?code=' . urlencode($codigo_acceso);
        } else {
             // En producción, usamos el host tal cual (https://...)
             $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
             // Si host_frontend no tiene protocolo, lo añadimos. Pero host_frontend suele ser el hostname.
             // Mejor usamos la URL del backend actual si es posible, o construimos con el host del frontend si es lo que queremos.
             // Espera, login_qr.php está en el BACKEND.
             // Así que deberíamos usar la URL del backend, no del frontend.
             
             // Usamos la variable de entorno PUBLIC_API_URL si existe, o construimos.
             $backendUrl = $_ENV['PUBLIC_API_URL'] ?? getenv('PUBLIC_API_URL');
             if (!$backendUrl) {
                 // Fallback si no hay variable: intentar deducir o usar el host actual
                 $backendUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
             }
             $qrUrl = $backendUrl . '/login_qr.php?code=' . urlencode($codigo_acceso);
        }

        error_log("QR URL generada: $qrUrl (host original: $host_frontend, convertido a: $host_para_qr)");
        
        $filename = $dir . 'qr_' . $id_usuario . '.png';
        QRcode::png($qrUrl, $filename, QR_ECLEVEL_L, 4);

        require_once __DIR__ . '/enviar_correo.php';
        enviarCorreoConQR($correo, $nombre, $codigo_acceso, $filename);
    } catch (Exception $e) {
        error_log("Background error: " . $e->getMessage());
    }

    
    $conn->close();
    exit(0);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit(0);
}
?>
