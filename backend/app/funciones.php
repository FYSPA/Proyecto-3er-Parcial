<?php
// Función para generar código alfanumérico único
function generarCodigoAlfanumerico($longitud = 12) {
    $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }
    return $codigo;
}

// Función para verificar que el código sea único en la BD
function generarCodigoUnico($conn) {
    do {
        $codigo = generarCodigoAlfanumerico(12);
        $check = $conn->prepare("SELECT id FROM usuarios WHERE codigo_acceso = ?");
        $check->bind_param("s", $codigo);
        $check->execute();
        $result = $check->get_result();
        $check->close();
    } while ($result->num_rows > 0);
    
    return $codigo;
}
?>
