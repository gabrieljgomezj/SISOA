-- Añadir tablas para el módulo de Educación y Formación

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `cursos`
-- Almacena la definición de cada curso o programa de formación
-- --------------------------------------------------------
CREATE TABLE `cursos` (
  `id_curso` INT NOT NULL AUTO_INCREMENT,
  `nombre_curso` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `duracion_horas` DECIMAL(5,2) NULL, -- Duración en horas, puede ser decimal (ej. 8.5)
  `frecuencia_recomendada` VARCHAR(100) COLLATE utf8mb4_unicode_ci NULL, -- Ej: 'Anual', 'Semestral', 'Cada 3 años'
  `activo` TINYINT(1) NOT NULL DEFAULT '1', -- 1: Activo, 0: Inactivo
  `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_curso`),
  UNIQUE KEY `idx_nombre_curso_unique` (`nombre_curso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `sesiones_formacion`
-- Almacena cada vez que un curso es impartido
-- --------------------------------------------------------
CREATE TABLE `sesiones_formacion` (
  `id_sesion` INT NOT NULL AUTO_INCREMENT,
  `id_curso` INT NOT NULL,
  `fecha_sesion` DATE NOT NULL,
  `hora_inicio` TIME NULL,
  `hora_fin` TIME NULL,
  `lugar` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL, -- Ej: 'Sala de Conferencias', 'Auditorio'
  `instructor` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `observaciones` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `estado_sesion` ENUM('Programada', 'Realizada', 'Cancelada') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Programada',
  `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sesion`),
  KEY `fk_sesion_curso` (`id_curso`),
  CONSTRAINT `fk_sesion_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Estructura de tabla para la tabla `participantes_sesion`
-- Registra los usuarios que asistieron a cada sesión de formación
-- --------------------------------------------------------
CREATE TABLE `participantes_sesion` (
  `id_participacion` INT NOT NULL AUTO_INCREMENT,
  `id_sesion` INT NOT NULL,
  `id_usuario` INT NOT NULL, -- El usuario que participó en la sesión
  `asistencia` TINYINT(1) NOT NULL DEFAULT '0', -- 1: Asistió, 0: No asistió
  `aprobado` TINYINT(1) NULL, -- NULL: Pendiente, 1: Aprobado, 0: No aprobado
  `calificacion` DECIMAL(5,2) NULL, -- Puntuación obtenida, si aplica
  `observaciones_participante` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_participacion`),
  UNIQUE KEY `idx_sesion_usuario_unique` (`id_sesion`,`id_usuario`), -- Un usuario no puede registrarse dos veces en la misma sesión
  KEY `fk_participante_sesion` (`id_sesion`),
  KEY `fk_participante_usuario` (`id_usuario`),
  CONSTRAINT `fk_participante_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_formacion` (`id_sesion`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_participante_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Opcional: Si los cursos se asocian a gerencias o centros de trabajo específicos
-- (esto dependerá de tu modelo de negocio)
-- --------------------------------------------------------
-- CREATE TABLE `curso_gerencia` (
--   `id_curso_gerencia` INT NOT NULL AUTO_INCREMENT,
--   `id_curso` INT NOT NULL,
--   `id_gerencia` INT NOT NULL,
--   PRIMARY KEY (`id_curso_gerencia`),
--   UNIQUE KEY `idx_curso_gerencia_unique` (`id_curso`,`id_gerencia`),
--   CONSTRAINT `fk_cg_curso` FOREIGN KEY (`id_curso`) REFERENCES `cursos` (`id_curso`) ON DELETE CASCADE ON UPDATE CASCADE,
--   CONSTRAINT `fk_cg_gerencia` FOREIGN KEY (`id_gerencia`) REFERENCES `gerencias` (`id_gerencia`) ON DELETE CASCADE ON UPDATE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CREATE TABLE `sesion_centro_trabajo` (
--   `id_sesion_centro` INT NOT NULL AUTO_INCREMENT,
--   `id_sesion` INT NOT NULL,
--   `id_centro` INT NOT NULL,
--   PRIMARY KEY (`id_sesion_centro`),
--   UNIQUE KEY `idx_sesion_centro_unique` (`id_sesion`,`id_centro`),
--   CONSTRAINT `fk_sct_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesiones_formacion` (`id_sesion`) ON DELETE CASCADE ON UPDATE CASCADE,
--   CONSTRAINT `fk_sct_centro` FOREIGN KEY (`id_centro`) REFERENCES `centros_trabajo` (`id_centro`) ON DELETE CASCADE ON UPDATE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;