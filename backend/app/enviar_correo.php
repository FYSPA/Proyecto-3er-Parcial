<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreoConQR($destinatario, $nombre, $codigo, $ruta_qr) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fernandosuarezflores05@gmail.com';
        $mail->Password = 'paxkyyqhllixplcx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('fernandosuarezflores05@gmail.com', 'CorreoProyecto');
        $mail->addAddress($destinatario, $nombre);
        
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Bienvenido - Tu código de acceso';
        
        $mail->Body = "
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
                            <img src='cid:qrimage' alt='Código QR' style='max-width: 250px;'>
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
        
        $mail->addEmbeddedImage($ruta_qr, 'qrimage');
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$mail->ErrorInfo}");
        return false;
    }
}