# Backend PHP - API REST con Autenticación y QR

## 📋 Descripción

Backend PHP para el sistema de registro e inicio de sesión con códigos QR, integrado con Astro en el frontend.

---

## 🚀 Instalación

### 1️⃣ Clonar las dependencias

```bash
cd backend
git clone https://github.com/phpqrcode/phpqrcode.git phpqrcode
```

### 2️⃣ Crear carpetas necesarias

```bash
mkdir qrcodes
chmod 777 qrcodes
```

### 3️⃣ Configurar la Base de Datos

- Abre phpMyAdmin: `http://localhost/phpmyadmin`
- Crea una BD llamada `db_qr`
- Importa el esquema (o crea las tablas manualmente)

**Tabla `usuarios`:**
```sql
CREATE TABLE usuarios (
  id VARCHAR(50) PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  codigo_acceso VARCHAR(20) UNIQUE NOT NULL,
  verificado TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## 🔧 Configuración

### Variables de Entorno

Crea un archivo `.env` en `backend/` (opcional):

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=db_qr
```

### Configurar Email (SMTP)

En `backend/enviar_correo.php`, configura tus credenciales:

```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'tu-email@gmail.com';
$mail->Password = 'tu-contraseña-app';
```

---

## ▶️ Ejecución

### Opción 1: PHP Built-in Server (Recomendado)

```bash
php -S 0.0.0.0:8000
```

Luego accede a: `http://localhost:8000`

### Opción 2: XAMPP

- Coloca el proyecto en `htdocs/`
- Inicia Apache y MySQL
- Accede a: `http://localhost/tu-proyecto/backend`

---

## 📁 Estructura

```
backend/
├── phpqrcode/           # Librería para generar QR (descargable)
├── qrcodes/             # Códigos QR generados (se crea automáticamente)
├── registro.php         # Endpoint de registro
├── login.php            # Endpoint de login
├── login_qr.php         # Verificación de QR
├── api-login.php        # API para verificar códigos
├── verificacion.php     # Página de verificación de código
├── enviar_correo.php    # Sistema de emails
├── funciones.php        # Funciones auxiliares
├── composer.json        # Dependencias (opcional)
└── error.log            # Log de errores
```

---

## 🔌 Endpoints

| Método | URL | Descripción |
|--------|-----|-------------|
| POST | `/registro.php` | Registrar usuario |
| POST | `/login.php` | Iniciar sesión |
| GET | `/login_qr.php?code=...` | Verificar QR |
| POST | `/api-login.php` | API para verificar código manual |

---

## 🐛 Troubleshooting

### ❌ Error: "Connection refused"
- Verifica que XAMPP/MySQL está corriendo
- Asegúrate que la BD `db_qr` existe

### ❌ Error: "phpqrcode not found"
```bash
git clone https://github.com/phpqrcode/phpqrcode.git phpqrcode
```

### ❌ Error: "Permission denied" en qrcodes/
```bash
chmod 777 qrcodes/
```

### ❌ QR no se genera
- Verifica que `/qrcodes` tiene permisos de escritura
- Revisa `error.log`

---

## 📧 Email Configuration

Para usar Gmail:
1. Activa "Contraseñas de aplicación" en tu cuenta Google
2. Usa la contraseña generada en `enviar_correo.php`

---

## 🔒 Seguridad

- Las contraseñas se almacenan con `password_hash()` (BCRYPT)
- Los códigos QR se generan con 12 caracteres aleatorios
- Los códigos de acceso expiran después de usarse

---

## 📝 Licencia

Este proyecto está bajo licencia MIT.

---

## 👨‍💻 Autor

Creado como parte de un proyecto educativo.