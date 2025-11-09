<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Conexión centralizada a base de datos
require_once __DIR__ . '/../config/db.php';

$codigo = $_POST['codigo'] ?? '';

if (empty($codigo)) {
    echo json_encode(['success' => false, 'message' => 'Código requerido']);
    exit();
}

// Buscar usuario por código
$stmt = $conn->prepare("SELECT id, nombre, correo FROM usuarios WHERE codigo_acceso = ?");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    
    // Actualizar verificación
    $updateStmt = $conn->prepare("UPDATE usuarios SET verificado = 1 WHERE id = ?");
    $updateStmt->bind_param("s", $usuario['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    echo json_encode([
        'success' => true,
        'user_id' => $usuario['id'],
        'user_nombre' => $usuario['nombre'],
        'user_correo' => $usuario['correo']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Código no válido o expirado']);
}

$stmt->close();
$conn->close();
?>
