<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: text/html; charset=utf-8');

$codigo = $_GET['code'] ?? '';

if (empty($codigo)) {
    http_response_code(400);
    echo "Código no válido";
    exit();
}

$servername = "localhost";
$username = "root";
$password_db = "";
$dbname = "db_qr";

$conn = new mysqli($servername, $username, $password_db, $dbname);

if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
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
    
    $stmt->close();
    $conn->close();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciando sesión...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        p {
            color: #666;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Sesión iniciada</h1>
        <div class="spinner"></div>
        <p>Redirigiendo...</p>
    </div>

        <script>
            // Datos del usuario
            const usuario = {
                id: '<?php echo htmlspecialchars($usuario['id']); ?>',
                nombre: '<?php echo htmlspecialchars($usuario['nombre']); ?>',
                correo: '<?php echo htmlspecialchars($usuario['correo']); ?>'
            };

            console.log('✅ Usuario:', usuario);
            console.log('🌐 Host detectado:', window.location.hostname);

            // Guardar en localStorage
            localStorage.setItem('user_id', usuario.id);
            localStorage.setItem('user_nombre', usuario.nombre);
            localStorage.setItem('user_correo', usuario.correo);
            localStorage.setItem('logged_in', 'true');

            // Redirigir a mainPage (detecta automáticamente el host)
            // Si viene de localhost, va a localhost:4321
            // Si viene de IP, va a IP:4321
            setTimeout(() => {
                // Detectar host automáticamente
                const astroHost = window.location.hostname === 'localhost'
                    ? 'http://localhost:4321'
                    : `http://${window.location.hostname}:4321`;
                
                console.log('📍 Redirigiendo a:', astroHost + '/mainPage');
                window.location.href = astroHost + '/mainPage';
            }, 500);
        </script>

</body>
</html>
<?php
} else {
    $stmt->close();
    $conn->close();
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            color: #c62828;
        }
        a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>❌ Código no válido o expirado</h1>
        <p>El código QR que intentaste usar no es válido.</p>
        <a href="/LoginRegisterPages/LoginPage">Volver al login</a>
    </div>
</body>
</html>
<?php
}
?>
