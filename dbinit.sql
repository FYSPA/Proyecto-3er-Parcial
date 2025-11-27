USE proyecto;

CREATE TABLE IF NOT EXISTS usuarios (
    id VARCHAR(50) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL DEFAULT NULL, 
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id VARCHAR(50) NOT NULL,
    token VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL COMMENT 'Ej: password_reset, qr_login, email_verify',
    usado TINYINT(1) DEFAULT 0 COMMENT '0: No usado, 1: Ya usado',
    expira_en TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Restricciones y claves foráneas
    CONSTRAINT fk_usuario_token FOREIGN KEY (usuario_id) 
        REFERENCES usuarios(id) 
        ON DELETE CASCADE, -- Si se borra el usuario, se borran sus tokens
        
    -- Índices para búsqueda rápida
    INDEX idx_token (token),
    INDEX idx_usuario_tipo (usuario_id, tipo)
);