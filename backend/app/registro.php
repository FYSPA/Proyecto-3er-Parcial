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
    
    $servername = "localhost";
    $username = "root";
    $password_db = "";
    $dbname = "db_qr";

    $conn = new mysqli($servername, $username, $password_db, $dbname);
    if ($conn->connect_error) {
        throw new Exception('BD: ' . $conn->connect_error);
    }

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
        $qrUrl = 'http://' . $host_para_qr . ':8000/app/login_qr.php?code=' . urlencode($codigo_acceso);

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
