<!-- Variables esperadas: $nombre, $codigo -->
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
            <h1>¡Bienvenido, <?php echo htmlspecialchars($nombre); ?>!</h1>
        </div>
        <div class='content'>
            <p>Tu cuenta ha sido creada exitosamente. Puedes iniciar sesión de dos formas:</p>
            
            <div class='qr-section'>
                <h2>Escanea este código QR</h2>
                <!-- Notar el cid:codigo_qr que coincide con lo que enviará el controller -->
                <img src='cid:codigo_qr' alt='Código QR' style='max-width: 250px;'>
                <p style='color: #666; font-size: 14px;'>Escanea con tu cámara para iniciar sesión automáticamente</p>
            </div>
            
            <h2 style='text-align: center;'>O usa tu código de acceso:</h2>
            <div class='code-box'>
                <?php echo htmlspecialchars($codigo); ?>
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