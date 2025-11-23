<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

require_once __DIR__ . '/../config/cors.php';

try {
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

    // --- Generación de QR y Envío de Correo ---
    $emailSent = false;
    $emailError = null;

    try {
        require_once __DIR__ . '/../phpqrcode/qrlib.php';
        
        $dir = __DIR__ . '/../qrcodes/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $host_para_qr = $host_frontend === 'localhost' ? gethostbyname(gethostname()) : $host_frontend;
        
        // Lógica de URL del QR
        if ($host_frontend === 'localhost' || strpos($host_frontend, '127.0.0.1') !== false) {
             $qrUrl = 'http://' . $host_para_qr . ':8000/login_qr.php?code=' . urlencode($codigo_acceso);
        } else {
             $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
             $backendUrl = $_ENV['PUBLIC_API_URL'] ?? getenv('PUBLIC_API_URL');
             if (!$backendUrl) {
                 $backendUrl = $protocol . "://" . $_SERVER['HTTP_HOST'];
             }
             $qrUrl = $backendUrl . '/login_qr.php?code=' . urlencode($codigo_acceso);
        }

        error_log("QR URL generada: $qrUrl");
        
        $filename = $dir . 'qr_' . $id_usuario . '.png';
        QRcode::png($qrUrl, $filename, QR_ECLEVEL_L, 4);

        require_once __DIR__ . '/enviar_correo.php';
        $emailSent = enviarCorreoConQR($correo, $nombre, $codigo_acceso, $filename);
        
        if (!$emailSent) {
            $emailError = "La función devolvió false (posible error SMTP)";
            error_log("Fallo el envio de correo a $correo");
        }

    } catch (Exception $e) {
        error_log("Error generando QR o enviando correo: " . $e->getMessage());
        $emailSent = false;
        $emailError = $e->getMessage();
    }

    $conn->close();
    
    // Enviar respuesta final
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Registro exitoso',
        'user_id' => $id_usuario,
        'codigo_acceso' => $codigo_acceso,
        'email_sent' => $emailSent,
        'email_error' => $emailError
    ]);
    exit(0);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit(0);
}
?>
