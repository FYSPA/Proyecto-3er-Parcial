<?php
require_once __DIR__ . '/../vendor/autoload.php';

// use Resend\Resend; // La clase Resend está en el namespace global

function enviarCorreoConQR($destinatario, $nombre, $codigo, $ruta_qr, $debug = false) {
    $logFile = __DIR__ . '/../email_debug.log';
    $log = function($msg) use ($logFile) {
        file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
    };

    $log("Iniciando enviarCorreoConQR para: $destinatario");

    // Load local credentials if file exists (for localhost)
    if (file_exists(__DIR__ . '/local_env.php')) {
        include __DIR__ . '/local_env.php';
        $log("local_env.php cargado");
        $log("Claves en \$_ENV: " . implode(', ', array_keys($_ENV)));
    } else {
        $log("local_env.php no encontrado en " . __DIR__);
    }

    $resend_api_key = $_ENV['RESEND_API_KEY'] ?? $_SERVER['RESEND_API_KEY'] ?? getenv('RESEND_API_KEY');

    if (empty($resend_api_key)) {
        $log("ERROR: RESEND_API_KEY está vacía");
        error_log("Error: RESEND_API_KEY no está configurada.");
        if ($debug) echo "<h3>FATAL ERROR: RESEND_API_KEY no está configurada.</h3>";
        return false;
    } else {
        $log("RESEND_API_KEY encontrada (longitud: " . strlen($resend_api_key) . ")");
    }

    try {
        $log("Inicializando cliente Resend...");
        // La clase Resend está en el namespace global, así que usamos \Resend o simplemente Resend si no hay namespace actual.
        // Como este archivo no tiene namespace, Resend se refiere a \Resend.
        $resend = Resend::client($resend_api_key);
        $log("Cliente Resend inicializado.");

        // Leer el contenido del archivo QR
        $log("Verificando archivo QR en: $ruta_qr");
        if (!file_exists($ruta_qr)) {
            $log("ERROR: Archivo QR no existe: $ruta_qr");
            throw new Exception("El archivo QR no existe en la ruta: $ruta_qr");
        }
        $log("Archivo QR encontrado.");
        
        // Leer el contenido binario del archivo
        $qr_content = file_get_contents($ruta_qr);
        if ($qr_content === false) {
             $log("ERROR: No se pudo leer el archivo QR");
             throw new Exception("No se pudo leer el archivo QR");
        }
        $log("Contenido QR leído (" . strlen($qr_content) . " bytes).");

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

        $log("Intentando enviar correo...");
        
        // Codificar el contenido del archivo QR en base64 para el adjunto
        // Resend espera el contenido como un string, pero para imágenes incrustadas o adjuntos seguros, base64 es mejor si hay problemas de encoding.
        // Sin embargo, la librería Resend PHP usa Guzzle y debería manejar el body.
        // El error "Malformed UTF-8 characters" sugiere que json_encode falló al serializar el body del request.
        // La solución es codificar el contenido del archivo en base64 antes de pasarlo a la librería si esta no lo hace automáticamente,
        // O asegurarnos de que el array de attachments esté bien formado.
        
        // Revisando la documentación de Resend PHP SDK, los attachments se pasan como array de objetos/arrays.
        // Si el contenido es binario, json_encode fallará.
        // Debemos convertirlo a una lista de enteros o base64.
        // La librería Resend PHP parece esperar que 'content' sea un buffer (array de enteros) o string.
        // Si es string binario, json_encode falla.
        
        // Vamos a convertir el contenido binario a un array de bytes (enteros) que es lo que suele funcionar con JSON,
        // O mejor, usaremos base64 y esperamos que Resend lo decodifique?
        // La API de Resend acepta 'content' como buffer.
        
        // Intentemos con base64 pero chunk_split no es necesario para JSON.
        // Simplemente base64_encode.
        
        // NOTA: Si usamos 'cid:codigo_qr' en el HTML, el attachment debe tener 'content_id' => 'codigo_qr' (si la librería lo soporta)
        // O 'filename' => 'codigo_qr.png' y referenciarlo.
        // Resend API soporta 'content_id' para inline images?
        // Si no, simplemente lo adjuntamos.
        
        // Para solucionar el error de JSON, convertimos a array de bytes.
        $qr_bytes = array_values(unpack('C*', $qr_content));

        $result = $resend->emails->send([
            'from' => 'VGS <onboarding@resend.dev>',
            'to' => [$destinatario],
            'subject' => 'Bienvenido - Tu código de acceso',
            'html' => $htmlContent,
            'attachments' => [
                [
                    'filename' => 'codigo_qr.png',
                    'content' => $qr_bytes, // Enviar como array de bytes para evitar error UTF-8 en json_encode
                ]
            ]
        ]);

        $log("Correo enviado exitosamente. ID: " . ($result->id ?? 'N/A'));

        if ($debug) {
            echo "<pre>";
            print_r($result);
            echo "</pre>";
        }

        return true;

    } catch (Exception $e) {
        $log("EXCEPCIÓN: " . $e->getMessage());
        if ($debug) {
            echo "<h3>FATAL ERROR: " . $e->getMessage() . "</h3>";
        }
        error_log("Error al enviar correo con Resend: " . $e->getMessage());
        return false;
    }
}
?>