# Módulo de Gestión de Usuarios

## 1. Visión General

El módulo de Gestión de Usuarios permite a los administradores del sistema gestionar las cuentas de usuario. Esto incluye la creación, visualización, edición y eliminación de usuarios, así como la asignación de perfiles y la gestión del estado de actividad.

## 2. Estructura de Archivos

- `modules/users/index.php`: Página principal que lista los usuarios existentes, permite búsquedas y ofrece enlaces para añadir, editar y eliminar.
- `modules/users/add_user.php`: Formulario para añadir nuevos usuarios.
- `modules/users/edit_user.php`: Formulario para editar un usuario existente.
- `modules/users/process_user.php`: Script para manejar acciones de servidor como la eliminación de usuarios.
- `css/main.css`: Contiene estilos CSS para la presentación de tablas, formularios y elementos de interfaz de usuario.
- `js/main.js`: Contiene lógica JavaScript para interacciones de usuario como la confirmación de eliminación (modal) y la gestión de notificaciones.
- `includes/db_connection.php`: Establece la conexión a la base de datos.
- `includes/session_check.php`: Verifica la sesión del usuario para asegurar el acceso.

## 3. Base de Datos (Esquema Relevante)

Las siguientes tablas son cruciales para el funcionamiento de este módulo:

-   `usuarios`:
    -   `id_usuario` (PK, INT, AUTO_INCREMENT)
    -   `nombre` (VARCHAR)
    -   `apellido` (VARCHAR)
    -   `cedula` (VARCHAR, UNIQUE)
    -   `ficha` (VARCHAR, UNIQUE, NULLABLE)
    -   `correo` (VARCHAR, UNIQUE)
    -   `username` (VARCHAR, UNIQUE)
    -   `password` (VARCHAR)
    -   `activo` (TINYINT, 0=Inactivo, 1=Activo)
    -   `fecha_creacion` (DATETIME)
    -   `fecha_actualizacion` (DATETIME)
-   `perfiles`:
    -   `id_perfil` (PK, INT, AUTO_INCREMENT)
    -   `nombre_perfil` (VARCHAR, UNIQUE)
    -   `descripcion` (TEXT, NULLABLE)
-   `usuario_perfil`: (Tabla pivote para relación muchos a muchos)
    -   `id_usuario` (FK a `usuarios.id_usuario`)
    -   `id_perfil` (FK a `perfiles.id_perfil`)

## 4. Funcionalidades Implementadas

### 4.1. Listado y Búsqueda de Usuarios (`index.php`)

-   **Muestra una tabla paginada** con los usuarios existentes, incluyendo nombre, apellido, cédula, nombre de usuario, perfiles y estado.
-   Permite la **búsqueda de usuarios** por nombre, apellido, cédula, ficha o nombre de usuario.
-   Proporciona **enlaces para editar y eliminar** cada usuario.
-   Muestra **notificaciones** (éxito/error) al añadir, editar o eliminar usuarios.

### 4.2. Añadir Nuevo Usuario (`add_user.php`)

-   **Formulario de entrada de datos** para:
    -   Nombre, Apellido, Cédula (obligatorios, únicos).
    -   Ficha de Trabajo (opcional, única si se proporciona).
    -   Correo Electrónico (obligatorio, único, formato email).
    -   Nombre de Usuario (obligatorio, único).
    -   Contraseña (obligatoria, mínimo 6 caracteres, se guarda hasheada).
    -   Confirmación de Contraseña.
    -   Asignación de **uno o más perfiles** (checkboxes).
    -   Estado `Activo` (checkbox, por defecto `activo`).
-   **Validación del lado del servidor** para todos los campos.
-   **Hash de contraseña** (`password_hash()`) antes de almacenar.
-   **Inserción de perfiles** en `usuario_perfil` después de crear el usuario.

### 4.3. Editar Usuario (`edit_user.php`)

-   **Formulario prellenado** con los datos del usuario seleccionado por ID.
-   Permite la **modificación de todos los campos** (nombre, apellido, cédula, ficha, correo, nombre de usuario).
-   **Validaciones de unicidad** que excluyen al usuario actual.
-   **Actualización de contraseña opcional**: Solo se cambia si se ingresan nuevos valores.
-   **Gestión de perfiles**: Elimina los perfiles anteriores del usuario y reinserta los perfiles seleccionados en una **transacción de base de datos** para asegurar la integridad.
-   Permite cambiar el estado `Activo`/`Inactivo`.

### 4.4. Eliminar Usuario (`process_user.php?action=delete&id=...`)

-   Maneja la lógica de eliminación de un usuario.
-   Requiere confirmación a través de un modal en `index.php`.
-   Realiza una **transacción de base de datos** para:
    1.  Eliminar los registros del usuario en `usuario_perfil`.
    2.  Eliminar el usuario de la tabla `usuarios`.
-   Redirige a `index.php` con un mensaje de éxito o error.

## 5. Permisos

-   Solo los usuarios con el perfil **`Administrador`** pueden acceder a las páginas de Gestión de Usuarios (`index.php`, `add_user.php`, `edit_user.php`) y ejecutar las acciones de `process_user.php`. El acceso no autorizado redirige al dashboard con un mensaje de error.

## 6. Consideraciones de Seguridad

-   **Validación y saneamiento de entradas**: Todos los datos de entrada del usuario son validados y saneados para prevenir ataques como inyección SQL.
-   **Consultas preparadas**: Se utilizan sentencias preparadas (prepared statements) para todas las interacciones con la base de datos, lo que mitiga la inyección SQL.
-   **Hash de contraseñas**: Las contraseñas se almacenan con `password_hash()`, lo que las hace seguras frente a ataques de fuerza bruta o filtraciones de base de datos.
-   **Control de sesiones y autenticación**: El acceso a las páginas está protegido por un sistema de verificación de sesión y roles (`session_check.php`).
-   **HTML Special Chars**: Se utiliza `htmlspecialchars()` para prevenir ataques XSS al mostrar datos provenientes de la base de datos.

## 7. Próximos Pasos (Fase 2)

- Implementación de los módulos de mantenimiento básico (Gerencias, Centros de Trabajo, Tipos de Condiciones Inseguras).