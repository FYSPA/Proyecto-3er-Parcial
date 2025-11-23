<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Iniciando prueba de correo...<br>";

try {
    require_once __DIR__ . '/enviar_correo.php';
    echo "enviar_correo.php incluido correctamente.<br>";
    
    // Dummy data
    $destinatario = "test@example.com";
    $nombre = "Test User";
    $codigo = "TEST1234";
    $ruta_qr = __DIR__ . '/../logoVGS.ico'; // Use an existing file as dummy QR
    
    // Check if dummy file exists, if not create one
    if (!file_exists($ruta_qr)) {
        file_put_contents('dummy_qr.png', 'dummy content');
        $ruta_qr = 'dummy_qr.png';
    }

    echo "Intentando enviar correo...<br>";
    $resultado = enviarCorreoConQR($destinatario, $nombre, $codigo, $ruta_qr);
    
    if ($resultado) {
        echo "Correo enviado exitosamente (o al menos la función retornó true).<br>";
    } else {
        echo "Fallo el envío de correo.<br>";
    }

} catch (Throwable $e) {
    echo "Excepción capturada: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}
?>
