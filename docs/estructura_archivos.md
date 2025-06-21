/
├── index.php                 // Punto de entrada de la aplicación, manejará la lógica de rutas y sesiones.
├── login.php                 // Página de inicio de sesión.
├── dashboard.php             // Página principal después del login.
├── config/                   // Archivos de configuración de la aplicación.
│   └── database.php          // Configuración de la conexión a la base de datos.
│   └── app_config.php        // Configuraciones generales del aplicativo (paleta de colores, rutas, etc.).
├── controllers/              // Lógica de negocio y manejo de peticiones.
│   └── AuthController.php    // Lógica para autenticación de usuarios.
│   └── UserController.php    // Lógica para la gestión de usuarios, perfiles y permisos.
├── models/                   // Modelos de datos y abstracción de la base de datos.
│   └── User.php              // Clase para interactuar con la tabla de usuarios.
│   └── Profile.php           // Clase para interactuar con la tabla de perfiles.
│   └── Permission.php        // Clase para interactuar con la tabla de permisos.
├── views/                    // Archivos de interfaz de usuario (HTML y fragmentos PHP).
│   ├── layout/               // Plantillas y elementos comunes de diseño.
│   │   ├── header.php
│   │   ├── footer.php
│   │   └── sidebar.php       // Si implementamos una barra lateral más adelante.
│   ├── auth/                 // Vistas relacionadas con autenticación.
│   │   └── login_view.php
│   ├── admin/                // Vistas para el módulo de administración (usuarios, perfiles).
│   │   ├── users_view.php
│   │   └── profiles_view.php
│   └── partials/             // Pequeños fragmentos HTML/PHP reusables (e.g., notificaciones).
│       └── notifications.php
├── assets/                   // Archivos estáticos (CSS, JavaScript, imágenes, iconos).
│   ├── css/
│   │   └── style.css         // Estilos globales de la aplicación.
│   │   └── login.css         // Estilos específicos del login.
│   ├── js/
│   │   └── main.js           // Scripts JavaScript globales.
│   │   └── auth.js           // Scripts JavaScript para autenticación.
│   │   └── ajax_handler.js   // Para manejar peticiones AJAX de forma centralizada.
│   ├── img/
│   │   └── logo.svg          // Logo de la empresa.
│   ├── svg/                  // Iconos SVG.
│   │   └── icon-user.svg
│   │   └── icon-lock.svg
│   │   └── ...
├── docs/                     // Archivos de documentación (Markdown).
│   ├── dev/                  // Documentación para desarrolladores.
│   │   └── 01-estructura-proyecto.md
│   │   └── 02-base-de-datos.md
│   │   └── ...
│   └── user/                 // Documentación para usuarios finales.
│       └── 01-inicio-sesion.md
│       └── ...
└── .htaccess                 // Para reescritura de URLs (si es necesario para URLs amigables).