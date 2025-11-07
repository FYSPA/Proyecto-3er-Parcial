<?php
// Este archivo se ejecuta SOLO cuando lo llama registro.php
// No responde al navegador

error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../background.log');

$id_usuario = $argv[1] ?? '';
$nombre = $argv[2] ?? '';
$correo = $argv[3] ?? '';
$codigo_acceso = $argv[4] ?? '';

if (!$id_usuario) exit(1);

try {
    require_once __DIR__ . '/../phpqrcode/qrlib.php';

    $dir = __DIR__ . '/qrcodes/';
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    // Generar QR
    $qrUrl = 'http://localhost:8000/app/login_qr.php?code=' . urlencode($codigo_acceso);
    $filename = $dir . 'qr_' . $id_usuario . '.png';
    QRcode::png($qrUrl, $filename, QR_ECLEVEL_L, 4);

    // Enviar correo
    require_once __DIR__ . '/app/enviar_correo.php';
    enviarCorreoConQR($correo, $nombre, $codigo_acceso, $filename);

    error_log("✅ QR y correo enviados para: $correo");

} catch (Exception $e) {
    error_log("❌ Error background: " . $e->getMessage());
}

exit(0);
?>
