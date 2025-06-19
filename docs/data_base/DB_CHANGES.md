
---

### **`DB_CHANGES.md` - Registro de Cambios en la Base de Datos**

```markdown
# Registro de Cambios en la Base de Datos (db_ferrominera_sst.sql)

Este documento registra los cambios estructurales realizados en la base de datos `db_ferrominera_sst` para dar soporte a las nuevas funcionalidades del sistema Ferrominera SST, específicamente la adición de la tabla `tipos_condiciones`.

## 1. Nueva Tabla: `tipos_condiciones`

**Propósito:** Almacenar los diferentes tipos de condiciones de seguridad (ej., "Condición Insegura", "Acto Inseguro") que serán utilizados para clasificar los eventos SST dentro del sistema. Esto permite una gestión estandarizada y configurable de las categorías de incidentes.

**Script SQL para Creación:**

```sql
--
-- Estructura de tabla para la tabla `tipos_condiciones`
--

CREATE TABLE `tipos_condiciones` (
  `id_tipo_condicion` INT NOT NULL AUTO_INCREMENT,
  `codigo_tipo` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE, -- Código único para el tipo de condición (ej. "INSEG-001")
  `nombre_tipo` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL, -- Nombre descriptivo (ej. "Acto Inseguro", "Condición Subestándar")
  `descripcion` TEXT COLLATE utf8mb4_unicode_ci NULL, -- Descripción detallada del tipo de condición
  `activo` TINYINT(1) NOT NULL DEFAULT '1', -- 1 para activo, 0 para inactivo
  `creado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tipo_condicion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para la tabla `tipos_condiciones` (opcional, pero recomendado para rendimiento)
--
ALTER TABLE `tipos_condiciones` ADD INDEX `idx_nombre_tipo` (`nombre_tipo`);
ALTER TABLE `tipos_condiciones` ADD INDEX `idx_codigo_tipo` (`codigo_tipo`);

--
-- Comentarios para la tabla (opcional, para documentación en DB)
--
ALTER TABLE `tipos_condiciones` COMMENT = 'Tabla para almacenar los diferentes tipos de condiciones de seguridad (inseguras, actos, etc.)';
