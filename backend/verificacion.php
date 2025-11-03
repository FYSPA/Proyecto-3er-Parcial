<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Código</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .info-text {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin: 20px 0;
        }
        .form-group {
            margin: 20px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
            text-align: left;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #667eea;
            border-radius: 10px;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #764ba2;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.3);
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            display: none;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            display: none;
        }
        .instructions {
            background: #e3f2fd;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
            font-size: 14px;
            color: #333;
        }
        .loading {
            display: none;
            text-align: center;
            color: #667eea;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>📱 Verificación de Código</h1>
        <p class="info-text">Escanea el código QR enviado a tu correo o ingresa el código manualmente</p>
        
        <div class="instructions">
            <strong>¿Cómo verificarte?</strong>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li>Escanea el código QR con tu cámara</li>
                <li>O ingresa el código de 12 caracteres aquí abajo</li>
            </ul>
        </div>
        
        <div id="errorDiv" class="error"></div>
        <div id="successDiv" class="success"></div>
        
        <form id="verificacionForm">
            <div class="form-group">
                <label for="codigo">Ingresa tu código de acceso:</label>
                <input 
                    type="text" 
                    name="codigo" 
                    id="codigo" 
                    placeholder="HAWU3XPQNAA0"
                    maxlength="20"
                    required
                    autocomplete="off"
                >
            </div>
            <button type="submit" class="btn-submit" id="submitBtn">Verificar</button>
        </form>
        
        <div class="loading" id="loadingDiv">
            <div class="spinner"></div>
            <p>Verificando código...</p>
        </div>
        
        <div style="margin: 30px 0; display: flex; align-items: center; gap: 10px; color: #999;">
            <span style="flex: 1; height: 1px; background: #ddd;"></span>
            O
            <span style="flex: 1; height: 1px; background: #ddd;"></span>
        </div>
        
        <p class="info-text" style="color: #999; font-size: 12px;">
            El código QR está en el correo que recibiste. Escanéalo con tu cámara para iniciar sesión automáticamente.
        </p>
    </div>

    <script>
        const form = document.getElementById('verificacionForm');
        const codigoInput = document.getElementById('codigo');
        const submitBtn = document.getElementById('submitBtn');
        const errorDiv = document.getElementById('errorDiv');
        const successDiv = document.getElementById('successDiv');
        const loadingDiv = document.getElementById('loadingDiv');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const codigo = codigoInput.value.trim().toUpperCase();
            console.log('Código ingresado:', codigo);
            
            if (!codigo) {
                mostrarError('Por favor ingresa un código');
                return;
            }

            submitBtn.disabled = true;
            loadingDiv.style.display = 'block';
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('codigo', codigo);

                console.log('Enviando código a api-login.php...');
                const response = await fetch('http://localhost:8000/api-login.php', {
                    method: 'POST',
                    body: formData
                });

                console.log('Respuesta:', response.status);
                const data = await response.json();
                console.log('Datos recibidos:', data);

                if (data.success) {
                    console.log('Código válido, guardando datos...');
                    localStorage.setItem('user_id', data.user_id);
                    localStorage.setItem('user_nombre', data.user_nombre);
                    localStorage.setItem('user_correo', data.user_correo);
                    localStorage.setItem('logged_in', 'true');

                    mostrarExito('✅ Código verificado correctamente');
                    
                    setTimeout(() => {
                        console.log('Redirigiendo a mainPage...');
                        window.location.href = 'http://localhost:4321/MainPageLogeado/landingMainPage';
                    }, 1500);
                } else {
                    console.log('Error:', data.message);
                    mostrarError(data.message || 'Código no válido');
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Error catch:', error);
                mostrarError('Error: ' + error.message);
                submitBtn.disabled = false;
            } finally {
                loadingDiv.style.display = 'none';
            }
        });

        function mostrarError(mensaje) {
            errorDiv.textContent = mensaje;
            errorDiv.style.display = 'block';
        }

        function mostrarExito(mensaje) {
            successDiv.textContent = mensaje;
            successDiv.style.display = 'block';
        }

        codigoInput.focus();
    </script>
</body>
</html>
