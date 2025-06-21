-- Creación de la base de datos
CREATE DATABASE IF NOT EXISTS db_ferrominera_sst
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos recién creada
USE db_ferrominera_sst;

-- Tabla para Perfiles (Administrador, Operador, Consultor)
CREATE TABLE IF NOT EXISTS perfiles (
    id_perfil INT AUTO_INCREMENT PRIMARY KEY,
    nombre_perfil VARCHAR(50) NOT NULL UNIQUE COMMENT 'Nombre único del perfil (ej. Administrador, Operador)',
    descripcion TEXT COMMENT 'Descripción del perfil'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para Usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_perfil INT NOT NULL,
    nombre_usuario VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre de usuario para el login',
    password VARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada del usuario',
    nombre_completo VARCHAR(200) NOT NULL COMMENT 'Nombre y apellido del usuario',
    correo_electronico VARCHAR(100) UNIQUE COMMENT 'Correo electrónico del usuario',
    activo BOOLEAN DEFAULT TRUE COMMENT 'Indica si el usuario está activo o inactivo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_perfil) REFERENCES perfiles(id_perfil) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla para Permisos (detalla las acciones que un perfil puede realizar)
CREATE TABLE IF NOT EXISTS permisos (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,
    nombre_permiso VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre único del permiso (ej. crear_condicion_insegura, ver_reportes_accidente)',
    descripcion TEXT COMMENT 'Descripción detallada del permiso'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de relación N:M entre Perfiles y Permisos
CREATE TABLE IF NOT EXISTS perfil_permiso (
    id_perfil INT NOT NULL,
    id_permiso INT NOT NULL,
    PRIMARY KEY (id_perfil, id_permiso),
    FOREIGN KEY (id_perfil) REFERENCES perfiles(id_perfil) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_permiso) REFERENCES permisos(id_permiso) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas de configuración inicial (ejemplo, se añadirán más en la Fase 2)
CREATE TABLE IF NOT EXISTS gerencias (
    id_gerencia INT AUTO_INCREMENT PRIMARY KEY,
    codigo_gerencia VARCHAR(10) NOT NULL UNIQUE COMMENT 'Código único de la gerencia (ej. GMIN, GPROD)',
    nombre_gerencia VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre completo de la gerencia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS centros_trabajo (
    id_centro_trabajo INT AUTO_INCREMENT PRIMARY KEY,
    codigo_centro VARCHAR(10) NOT NULL UNIQUE COMMENT 'Código único del centro de trabajo',
    nombre_centro VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre completo del centro de trabajo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales para perfiles y un usuario administrador de ejemplo
INSERT INTO perfiles (nombre_perfil, descripcion) VALUES
('Administrador', 'Acceso total al sistema y configuraciones'),
('Operador', 'Acceso para registrar y gestionar condiciones, accidentes y formación'),
('Consultor', 'Acceso solo para visualización de reportes y dashboard');

-- Insertar un usuario administrador inicial (contraseña: adminpass - se debe cambiar después)
-- La contraseña 'adminpass' se debe hashear antes de insertarla en un entorno real.
-- Aquí, la hashearemos usando PHP password_hash() para el ejemplo.
-- VALUES (1, 'admin', '$2y$10$92hG.Qv8hZ5X.j.12345.uC.y.F6V.23456789012345678901234567890', 'Administrador General', 'admin@ferrominera.com', TRUE);
-- Nota: La contraseña 'adminpass' hasheada para el ejemplo es generada para ilustrar.
-- En la implementación real, PHP la hasheará antes de insertar.
-- Por ejemplo, password_hash('adminpass', PASSWORD_BCRYPT)
INSERT INTO usuarios (id_perfil, nombre_usuario, password, nombre_completo, correo_electronico, activo) VALUES
(1, 'admin', '$2y$10$tJ9tD/pXoY4hWf.f.E.f.h.z.c.2.d.3.e.f.g.h.i.j.k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z.', 'Administrador del Sistema', 'admin@ferrominera.com', TRUE);

-- Insertar permisos básicos (se expandirán a medida que desarrollemos los módulos)
INSERT INTO permisos (nombre_permiso, descripcion) VALUES
('acceso_admin', 'Acceso al módulo de administración'),
('acceso_operador', 'Acceso a funcionalidades de operador (registro y gestión)'),
('acceso_consultor', 'Acceso a funcionalidades de consultor (solo vista)'),
('crear_usuario', 'Permiso para crear nuevos usuarios'),
('editar_usuario', 'Permiso para editar usuarios existentes'),
('eliminar_usuario', 'Permiso para eliminar usuarios'),
('ver_dashboard', 'Permiso para ver el dashboard');

-- Asignar permisos al perfil de Administrador
INSERT INTO perfil_permiso (id_perfil, id_permiso) VALUES
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Administrador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'acceso_admin')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Administrador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'acceso_operador')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Administrador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'acceso_consultor')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Administrador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'crear_usuario')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Administrador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'editar_usuario')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Administrador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'eliminar_usuario')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Administrador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'ver_dashboard'));

-- Asignar permisos al perfil de Operador
INSERT INTO perfil_permiso (id_perfil, id_permiso) VALUES
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Operador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'acceso_operador')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Operador'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'ver_dashboard'));

-- Asignar permisos al perfil de Consultor
INSERT INTO perfil_permiso (id_perfil, id_permiso) VALUES
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Consultor'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'acceso_consultor')),
((SELECT id_perfil FROM perfiles WHERE nombre_perfil = 'Consultor'), (SELECT id_permiso FROM permisos WHERE nombre_permiso = 'ver_dashboard'));