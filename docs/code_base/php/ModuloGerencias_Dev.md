# Módulo de Gerencias: Documentación para Desarrolladores

## 1. Propósito del Módulo

El Módulo de Gerencias tiene como objetivo principal la gestión centralizada de las áreas de gerencia dentro de la estructura organizacional de Ferrominera. Actúa como una entidad maestra para categorizar y relacionar registros de otros módulos (ej., usuarios, centros de trabajo), garantizando la integridad referencial y la organización de la información operativa.

## 2. Estructura de Archivos del Módulo

El código fuente del módulo reside en el directorio `modules/gerencias/` con la siguiente estructura:

* `index.php`:
    * **Función:** Página principal del módulo. Muestra un listado paginado y con capacidad de búsqueda de todas las gerencias registradas.
    * **Lógica:** Realiza consultas `SELECT` a la tabla `gerencias`. Implementa lógica de paginación (`LIMIT`, `OFFSET`) y búsqueda (`LIKE`).
    * **Dependencias:** `includes/session_check.php`, `includes/db_connection.php`, `css/main.css`, `js/main.js`.
    * **Permisos:** Requiere perfil 'Administrador'.

* `add_gerencia.php`:
    * **Función:** Proporciona un formulario para la creación de nuevas entradas de gerencias.
    * **Lógica:**
        * Procesa solicitudes `POST` para insertar datos en la tabla `gerencias`.
        * Implementa validaciones de entrada (`trim`, `empty`) y verificación de unicidad para `codigo_gerencia` y `nombre_gerencia` antes de la inserción.
        * Utiliza sentencias preparadas para la operación `INSERT`.
    * **Dependencias:** `includes/session_check.php`, `includes/db_connection.php`, `css/main.css`, `js/main.js`.
    * **Permisos:** Requiere perfil 'Administrador'.

* `edit_gerencia.php`:
    * **Función:** Ofrece un formulario para la modificación de registros de gerencias existentes.
    * **Lógica:**
        * En solicitud `GET` (con `id` como parámetro), recupera los datos de la gerencia de la base de datos para precargar el formulario.
        * En solicitud `POST`, procesa la actualización de los datos en la tabla `gerencias`.
        * Implementa validaciones de entrada y verificación de unicidad para `codigo_gerencia` y `nombre_gerencia`, excluyendo el `id_gerencia` actual para permitir la persistencia del mismo código/nombre.
        * Utiliza sentencias preparadas para la operación `UPDATE`.
    * **Dependencias:** `includes/session_check.php`, `includes/db_connection.php`, `css/main.css`, `js/main.js`.
    * **Permisos:** Requiere perfil 'Administrador'.

* `process_gerencia.php`:
    * **Función:** Backend para procesar acciones específicas, actualmente solo la eliminación de gerencias.
    * **Lógica:**
        * Recibe el `id` de la gerencia a eliminar a través de un parámetro `GET`.
        * Utiliza sentencias preparadas para la operación `DELETE`.
        * Incluye manejo de errores específicos para violaciones de claves foráneas (`errno 1451`) para proporcionar feedback adecuado al usuario.
    * **Dependencias:** `includes/session_check.php`, `includes/db_connection.php`.
    * **Permisos:** Requiere perfil 'Administrador'.

## 3. Estructura de la Tabla de Base de Datos (`gerencias`)

La tabla `gerencias` es fundamental para este módulo y su esquema es el siguiente:

```sql
CREATE TABLE IF NOT EXISTS gerencias (
    id_gerencia INT AUTO_INCREMENT PRIMARY KEY,
    codigo_gerencia VARCHAR(20) NOT NULL UNIQUE,
    nombre_gerencia VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);