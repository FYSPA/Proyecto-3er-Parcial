# 📋 Guía de Instalación - Sistema de Autenticación QR

## 🎯 Requisitos previos

- **PHP 8.2+** instalado
- **Node.js y npm** instalados
- **MySQL/XAMPP** para la base de datos
- **Git** (para versionar)
- **Composer** (para dependencias PHP)

---

## 📥 Paso 1: Clonar o descargar el proyecto

```bash
git clone <tu-repositorio>
cd Proyecto-3er-Parcial
```

---

## 🔧 Paso 2: Configurar Frontend (Astro)

```bash
# Entra a la carpeta de Astro
cd mi-app-astro

# Instala las dependencias
npm install

# Salir de la carpeta
cd ..
```

---

## 🔧 Paso 3: Configurar Backend (PHP)

```bash
# Entra a la carpeta backend
cd backend

# Instala PHPMailer con Composer
composer require phpmailer/phpmailer

# Verifica que Composer instaló correctamente
php -m | find "mysqli"
```

### Si PHPMailer no se instala:

```bash
# Descargar Composer si no lo tienes
# Ve a https://getcomposer.org/download/

# Luego intenta nuevamente
composer install
```

---

## 📦 Paso 4: Descargar PHPQRCode

1. Descarga desde: `https://sourceforge.net/projects/phpqrcode/files/`
2. Extrae la carpeta `phpqrcode` en `backend/`

Tu carpeta backend debe verse así:

```
backend/
├── phpqrcode/          ← Librería de QR
├── vendor/             ← Creado por Composer
├── qrcodes/            ← Se crea automáticamente
├── api-login.php
├── login.php
├── login_qr.php
├── registro.php
├── verificacion.php
├── funciones.php
├── enviar_correo.php
├── composer.json
└── composer.lock
```

---

## 🗄️ Paso 5: Configurar Base de Datos

### Con phpMyAdmin (si usas XAMPP):

1. Abre `http://localhost/phpmyadmin`
2. Crea una base de datos llamada `db_qr`
3. Ejecuta esta query:

```sql
CREATE TABLE usuarios (
  id VARCHAR(50) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  codigo_acceso VARCHAR(20) UNIQUE NOT NULL,
  verificado BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### O directamente en MySQL:

```bash
mysql -u root -p

# Dentro de MySQL:
CREATE DATABASE db_qr;
USE db_qr;

CREATE TABLE usuarios (
  id VARCHAR(50) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  codigo_acceso VARCHAR(20) UNIQUE NOT NULL,
  verificado BOOLEAN DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

EXIT;
```

---

## ⚙️ Paso 6: Configurar Gmail (para enviar correos)

1. Ve a tu cuenta de Google
2. Activa la "Verificación en dos pasos"
3. Genera una contraseña de aplicación
4. En `backend/enviar_correo.php`, cambia:

```php
$mail->Username = 'TU_EMAIL@gmail.com';      // ← Tu correo
$mail->Password = 'TU_CONTRASEÑA_APP';       // ← Contraseña de app
```

---

## 🔌 Paso 7: Habilitar extensión GD en PHP

1. Abre `C:\xampp\php\php.ini`
2. Busca (Ctrl+F): `;extension=gd`
3. Descomenta la línea (quita el `;`):

```ini
extension=gd
```

4. Guarda el archivo

---

## ✅ Paso 8: Verificar instalación

```bash
# Verifica PHP
php -v

# Verifica GD está habilitado
php -m | find "gd"

# Verifica Composer
composer --version

# Verifica MySQL
mysql -u root -p -e "SELECT 1;"
```

---

## 🚀 Paso 9: Ejecutar los servidores

### Terminal 1 - Inicia MySQL (si usas XAMPP):

```bash
# En XAMPP, inicia el servicio MySQL desde la GUI
# O si tienes MySQL instalado:
mysql -u root -p
```

### Terminal 2 - Inicia el servidor PHP:

```bash
cd C:\Users\Lenovo T480\Desktop\Proyecto-3er-Parcial\backend
php -S localhost:8000
```

Deberías ver:
```
[Mon Nov 03 12:00:00 2025] PHP 8.2.12 Development Server (http://localhost:8000) started
```

### Terminal 3 - Inicia Astro:

```bash
cd C:\Users\Lenovo T480\Desktop\Proyecto-3er-Parcial\mi-app-astro
npm run dev
```

Deberías ver:
```
  ➔ Local:    http://localhost:4321/
```

---

## 🌐 Acceder a la aplicación

- **Frontend**: `http://localhost:4321`
- **Backend PHP**: `http://localhost:8000` (no accedes directamente)
- **phpMyAdmin**: `http://localhost/phpmyadmin`

---

## 📝 Flujos de uso

### Registrarse:

1. Ve a `http://localhost:4321/LoginRegisterPages/RegisterPage`
2. Llena el formulario
3. Recibirás un correo con el código QR
4. En la página de verificación, puedes:
   - Escanear el QR con la cámara
   - Ingresar el código manualmente

### Iniciar sesión:

1. Ve a `http://localhost:4321/LoginRegisterPages/LoginPage`
2. Ingresa email y contraseña
3. Se te redirige a `/mainPage`

---

## 🛠️ Comandos útiles

```bash
# Reiniciar PHP server
# Presiona Ctrl+C en la terminal, luego:
php -S localhost:8000

# Ver logs de PHP
# Los errores aparecen en la terminal donde corre PHP

# Limpiar caché de Astro
rm -r .astro dist/
npm run dev

# Resetear la BD
# En MySQL:
DROP DATABASE db_qr;
# Y vuelve a crear la tabla
```

---

## ⚠️ Problemas comunes

### Error: "Port 8000 is already in use"

```bash
# Encuentra qué está usando el puerto
netstat -ano | find ":8000"

# O usa un puerto diferente:
php -S localhost:8001
```

### Error: "CORS policy blocked"

✅ Ya está resuelto en los archivos PHP con los headers CORS

### Error: "ImageCreate() not found"

✅ Habilita la extensión GD en `php.ini` (paso 7)

### Error: "Duplicate entry for email"

Significa que el usuario ya existe. Usa otro email o borra la fila en phpMyAdmin.

---

## 📤 Subir a GitHub

```bash
cd C:\Users\Lenovo T480\Desktop\Proyecto-3er-Parcial\

git add .
git commit -m "feat: Sistema completo de autenticación con QR"
git push origin main
# O si usas master:
# git push origin master
```

---

## 📚 Estructura del proyecto

```
Proyecto-3er-Parcial/
├── mi-app-astro/
│   ├── src/
│   │   ├── pages/
│   │   │   ├── LoginRegisterPages/
│   │   │   │   ├── LoginPage.astro
│   │   │   │   └── RegisterPage.astro
│   │   │   ├── mainPage.astro
│   │   │   └── login-success.astro
│   │   ├── components/
│   │   │   ├── LoginComponent.astro
│   │   │   └── RegisterComponent.astro
│   │   └── styles/
│   ├── package.json
│   └── astro.config.mjs
│
├── backend/
│   ├── phpqrcode/           ← Librería QR
│   ├── vendor/              ← PHPMailer
│   ├── qrcodes/             ← Códigos QR generados
│   ├── api-login.php        ← Verificar código QR
│   ├── login.php            ← Login por email/contraseña
│   ├── login_qr.php         ← Escanear QR
│   ├── registro.php         ← Registrar usuario
│   ├── verificacion.php     ← Página de verificación
│   ├── funciones.php        ← Funciones auxiliares
│   ├── enviar_correo.php    ← Enviar correos
│   ├── composer.json
│   └── composer.lock
│
├── .gitignore
├── README.md
└── INSTALACION_GUIA.md      ← Este archivo
```

---

## ✨ ¡Listo!

Ya está todo configurado. Si tienes problemas, verifica los logs en las terminales donde corren los servidores.

¡Éxito! 🚀
