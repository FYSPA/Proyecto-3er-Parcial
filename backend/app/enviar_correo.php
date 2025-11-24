<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function enviarCorreoConQR($destinatario, $nombre, $codigo, $ruta_qr, $debug = false) {
    $logFile = __DIR__ . '/../email_debug.log';
    $log = function($msg) use ($logFile) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
    };

    $log("Iniciando enviarCorreoConQR (PHPMailer) para: $destinatario");

    // Cargar variables de entorno si no están cargadas
    if (!isset($_ENV['SMTP_HOST']) && file_exists(__DIR__ . '/../vendor/autoload.php')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();
            $log(".env cargado desde " . realpath(__DIR__ . '/../../'));
        } catch (Exception $e) {
            $log("Error cargando .env: " . $e->getMessage());
        }
    }

    // Fallback local_env.php (opcional)
    if (file_exists(__DIR__ . '/local_env.php')) {
        include __DIR__ . '/local_env.php';
        $log("local_env.php cargado (override)");
    }

    $mail = new PHPMailer(true);

    try {
        if ($debug) {
            $mail->SMTPDebug = 2; // Verbose debug output
            $mail->Debugoutput = function($str, $level) use ($log) {
                $log("SMTP: $str");
            };
        }

        // Recipients
        $mail->setFrom($mail->Username, 'VGS System');
        $mail->addAddress($destinatario, $nombre);

        // Attachments
        if (file_exists($ruta_qr)) {
            $mail->addAttachment($ruta_qr, 'codigo_qr.png');
            $log("Adjuntando QR desde: $ruta_qr");
        } else {
            $log("ERROR: Archivo QR no encontrado en $ruta_qr");
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenido - Tu código de acceso';
        
        // Embed image for HTML body using CID
        if (file_exists($ruta_qr)) {
            $mail->addEmbeddedImage($ruta_qr, 'codigo_qr', 'codigo_qr.png');
        }

        $htmlContent = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background: #f5f5f5; }
                    .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
                    .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px; }
                    .content { padding: 30px; }
                    .qr-section { text-align: center; margin: 20px 0; background: #f9f9f9; padding: 20px; border-radius: 10px; }
                    .code-box { background: #667eea; color: white; padding: 15px; font-size: 24px; font-weight: bold; letter-spacing: 3px; border-radius: 5px; margin: 20px 0; }
                    .footer { text-align: center; color: #999; font-size: 12px; margin-top: 20px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>¡Bienvenido, $nombre!</h1>
                    </div>
                    <div class='content'>
                        <p>Tu cuenta ha sido creada exitosamente. Puedes iniciar sesión de dos formas:</p>
                        
                        <div class='qr-section'>
                            <h2>Escanea este código QR</h2>
                            <img src='cid:codigo_qr' alt='Código QR' style='max-width: 250px;'>
                            <p style='color: #666; font-size: 14px;'>Escanea con tu cámara para iniciar sesión automáticamente</p>
                        </div>
                        
                        <h2 style='text-align: center;'>O usa tu código de acceso:</h2>
                        <div class='code-box'>
                            $codigo
                        </div>
                        
                        <p style='text-align: center; color: #666;'>
                            Guarda este código en un lugar seguro. Lo necesitarás para iniciar sesión.
                        </p>
                    </div>
                    <div class='footer'>
                        <p>Este correo fue enviado automáticamente, por favor no respondas.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->Body = $htmlContent;
        $mail->AltBody = "Bienvenido $nombre. Tu código de acceso es: $codigo";

        $log("Intentando enviar correo...");
        $mail->send();
        $log("Correo enviado exitosamente.");
        return true;

    } catch (Exception $e) {
        $log("EXCEPCIÓN PHPMailer: " . $mail->ErrorInfo);
        if ($debug) {
            echo "<h3>FATAL ERROR: " . $mail->ErrorInfo . "</h3>";
        }
        error_log("Error al enviar correo con PHPMailer: " . $mail->ErrorInfo);
        return false;
    }
}
?>