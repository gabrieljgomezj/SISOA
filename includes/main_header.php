<?php
/**
 * includes/main_header.php
 *
 * Este archivo contiene el código HTML y PHP para el encabezado principal
 * y el menú de navegación de la aplicación Ferrominera SST.
 *
 * Las opciones del menú se renderizan dinámicamente basándose en los perfiles
 * de usuario almacenados en la sesión.
 */

// Asegúrate de que la sesión ya esté iniciada en la página que incluye este archivo
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Asegúrate de que $_SESSION['perfiles'] exista y sea un array.
if (!isset($_SESSION['perfiles']) || !is_array($_SESSION['perfiles'])) {
    $_SESSION['perfiles'] = []; // Inicializar como array vacío para evitar errores
}

// Variables booleanas para simplificar las comprobaciones de perfiles
$isAdmin = in_array('Administrador', $_SESSION['perfiles']);
$isOperador = in_array('Operador', $_SESSION['perfiles']);
$isConsultor = in_array('Consultor', $_SESSION['perfiles']);

// **CÓDIGO MEJORADO Y ROBUSTO PARA DETERMINAR $root_path**
// Determina la ruta base del proyecto para enlaces (ej. /SISOA/ o /)
$root_path = '/';
// Define el nombre de la carpeta de tu proyecto. ¡AJUSTA ESTO SI ES NECESARIO!
$project_folder_name = 'SISOA';

// Obtener la URI actual, limpiarla de parámetros GET
$current_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determinar la ruta base del proyecto
if (!empty($project_folder_name) && strpos($current_uri_path, '/' . $project_folder_name . '/') !== false) {
    $root_path = '/' . $project_folder_name . '/';
} elseif (strpos($current_uri_path, '/index.php') !== false || strpos($current_uri_path, '/dashboard.php') !== false) {
    // Si estamos en la raíz del proyecto (sin subcarpeta específica)
    $root_path = '/';
}
// Asegura la barra final
$root_path = rtrim($root_path, '/') . '/';


// Función para determinar si un módulo está activo para marcar el menú
function is_active_module($current_path, $module_name, $root_base_path) {
    // Eliminar la barra inicial de $root_base_path si existe para que la comparación sea limpia
    $root_base_path = ltrim($root_base_path, '/');

    // Quitar la parte del $root_path de la $current_path para comparar
    $cleaned_current_path = str_replace($root_base_path, '', ltrim($current_path, '/'));

    // Verificar si la ruta actual comienza con la ruta esperada del módulo
    return strpos($cleaned_current_path, 'modules/' . $module_name) === 0;
}

?>
<header class="main-header">
    <a href="<?php echo $root_path; ?>dashboard.php" class="logo">
        <img src="<?php echo $root_path; ?>img/logo.png" alt="Logo Ferrominera">
        FERROMINERA SST
    </a>

    <button class="menu-toggle" aria-label="Toggle navigation">
        &#9776; </button>

    <nav class="main-nav">
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="<?php echo $root_path; ?>dashboard.php" class="nav-link <?php echo ($current_uri_path == $root_path || $current_uri_path == $root_path . 'dashboard.php') ? 'active' : ''; ?>">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($isAdmin): ?>
                <?php
                // Comprueba si alguna sub-ruta de 'admin' está activa
                $admin_active = is_active_module($current_uri_path, 'users', $root_path) ||
                                is_active_module($current_uri_path, 'roles', $root_path) ||
                                is_active_module($current_uri_path, 'gerencias', $root_path) ||
                                is_active_module($current_uri_path, 'centros_trabajo', $root_path);
                ?>
                <li class="nav-item has-submenu <?php echo $admin_active ? 'active' : ''; ?>">
                    <a href="#" class="nav-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <span>Panel de Administración</span>
                    </a>
                    <ul class="submenu <?php echo $admin_active ? 'active' : ''; ?>">
                        <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/users/index.php" class="<?php echo is_active_module($current_uri_path, 'users', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm0 2c2.21 0 4 1.79 4 4s-1.79 4-4 4-4-1.79-4-4 1.79-4 4-4zm0 14c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                Gestión de Usuarios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/centros_trabajo/index.php" class="<?php echo is_active_module($current_uri_path, 'centros_trabajo', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M20 6h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zM10 4h4v2h-4V4zm10 16H4V8h16v12z"/></svg>
                                Centros de Trabajo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/gerencias/index.php" class="<?php echo is_active_module($current_uri_path, 'gerencias', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20H4v-6h6v6zm0-8H4V4h6v8zm10 8h-6v-6h6v6zm0-8h-6V4h6v8z"/></svg>
                                Gerencias
                            </a>
                        </li>
                         <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/tipos_condiciones/index.php" class="<?php echo is_active_module($current_uri_path, 'tipos_condiciones', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zm0-8h14V7H7v2z"/></svg>
                                Tipos de Condiciones
                            </a>
                        </li>
                        <li class="nav-item has-submenu <?php echo (is_active_module($current_uri_path, 'submodulo1_admin', $root_path) || is_active_module($current_uri_path, 'submodulo2_admin', $root_path)) ? 'active' : ''; ?>">
                            <a href="#" class="nav-link">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                Más Opciones Admin
                            </a>
                            <ul class="submenu <?php echo (is_active_module($current_uri_path, 'submodulo1_admin', $root_path) || is_active_module($current_uri_path, 'submodulo2_admin', $root_path)) ? 'active' : ''; ?>">
                                <li>
                                    <a href="<?php echo $root_path; ?>modules/submodulo1_admin/index.php" class="<?php echo is_active_module($current_uri_path, 'submodulo1_admin', $root_path) ? 'active' : ''; ?>">
                                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                                        Submodulo Admin 1
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo $root_path; ?>modules/submodulo2_admin/index.php" class="<?php echo is_active_module($current_uri_path, 'submodulo2_admin', $root_path) ? 'active' : ''; ?>">
                                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm-.01 10.5L14 17h-4l-.01-2.5zM12 10l-4-4h8l-4 4z"/></svg>
                                        Submodulo Admin 2
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($isAdmin || $isOperador): ?>
                <?php
                $operaciones_active = is_active_module($current_uri_path, 'registro_incidentes', $root_path) ||
                                      is_active_module($current_uri_path, 'reportes_rapidos', $root_path); // Ejemplo
                ?>
                <li class="nav-item has-submenu <?php echo $operaciones_active ? 'active' : ''; ?>">
                    <a href="#" class="nav-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        <span>Operaciones</span>
                    </a>
                    <ul class="submenu <?php echo $operaciones_active ? 'active' : ''; ?>">
                        <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/registro_incidentes/index.php" class="<?php echo is_active_module($current_uri_path, 'registro_incidentes', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm-1 7V3.5L18.5 9H13z"/></svg>
                                Registro de Incidentes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/reportes_rapidos/index.php" class="<?php echo is_active_module($current_uri_path, 'reportes_rapidos', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"/></svg>
                                Reportes Rápidos
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($isAdmin || $isOperador || $isConsultor): ?>
                <?php
                $reportes_active = is_active_module($current_uri_path, 'reportes_condiciones', $root_path) ||
                                   is_active_module($current_uri_path, 'reportes_accidentes', $root_path);
                ?>
                <li class="nav-item has-submenu <?php echo $reportes_active ? 'active' : ''; ?>">
                    <a href="#" class="nav-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"/></svg>
                        <span>Reportes y Estadísticas</span>
                    </a>
                    <ul class="submenu <?php echo $reportes_active ? 'active' : ''; ?>">
                        <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/reportes_condiciones/index.php" class="<?php echo is_active_module($current_uri_path, 'reportes_condiciones', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                                Reporte Condiciones Inseguras
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo $root_path; ?>modules/reportes_accidentes/index.php" class="<?php echo is_active_module($current_uri_path, 'reportes_accidentes', $root_path) ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm-.01 10.5L14 17h-4l-.01-2.5zM12 10l-4-4h8l-4 4z"/></svg>
                                Reporte Accidentes de Trabajo
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a href="<?php echo $root_path; ?>logout.php" class="nav-link logout-link">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    <span>Cerrar Sesión</span>
                </a>
            </li>
        </ul>
    </nav>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mainNav = document.querySelector('.main-nav');
        const menuToggle = document.querySelector('.menu-toggle');

        // Toggle del menú principal en móvil
        if (menuToggle && mainNav) {
            menuToggle.addEventListener('click', function() {
                mainNav.classList.toggle('active');
            });
        }

        // Lógica para los submenús desplegables (funciona para cualquier nivel de anidación)
        document.querySelectorAll('.has-submenu > .nav-link').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault(); // Evita la navegación por defecto del enlace padre

                const parentLi = this.closest('.has-submenu');

                if (parentLi) {
                    // Si estamos en modo móvil, queremos que el comportamiento sea acordeón
                    // Si estamos en desktop, queremos el comportamiento hover/click
                    const isMobile = window.matchMedia("(max-width: 900px)").matches;

                    if (isMobile) {
                        // Comportamiento acordeón para móviles:
                        // Si el padre ya está activo, desactívalo.
                        // Si no, activa este padre y cierra los hermanos del mismo nivel.
                        const isActive = parentLi.classList.contains('active');

                        // Cierra todos los submenús hermanos del mismo nivel
                        parentLi.parentNode.querySelectorAll('.has-submenu.active').forEach(openParentLi => {
                            if (openParentLi !== parentLi) {
                                openParentLi.classList.remove('active');
                            }
                        });
                        // Toggle el actual
                        parentLi.classList.toggle('active', !isActive); // Activa si no estaba activo, desactiva si sí
                    } else {
                        // Comportamiento hover/click para desktop:
                        // Cierra todos los submenús hermanos del mismo nivel antes de abrir el nuevo
                        parentLi.parentNode.querySelectorAll('.has-submenu.active').forEach(openParentLi => {
                            if (openParentLi !== parentLi) {
                                openParentLi.classList.remove('active');
                            }
                        });
                        parentLi.classList.toggle('active');
                    }
                }
            });
        });

        // Cierra todos los submenús al hacer clic fuera del main-header
        document.addEventListener('click', function(event) {
            const header = document.querySelector('.main-header');
            const isMobile = window.matchMedia("(max-width: 900px)").matches;

            // En móvil, solo si el menú de hamburguesa NO está visible (es decir, el menú está abierto)
            // o si el clic fue fuera del header.
            if (!header.contains(event.target)) {
                 document.querySelectorAll('.has-submenu.active').forEach(parentLi => {
                    parentLi.classList.remove('active');
                 });
                 // También cierra el menú móvil si estaba abierto
                 if (isMobile && mainNav.classList.contains('active')) {
                     mainNav.classList.remove('active');
                 }
            }
        });


        // Asegurarse de que si se carga una página de un submenú, el menú padre esté abierto.
        // Esta lógica es crucial para que los submenús anidados también se abran automáticamente.
        document.querySelectorAll('.submenu a.active').forEach(activeSubItem => {
            let currentElement = activeSubItem;
            // Sube por la jerarquía hasta encontrar todos los padres .submenu y .has-submenu
            while (currentElement) {
                const parentSubmenu = currentElement.closest('.submenu');
                if (parentSubmenu) {
                    parentSubmenu.classList.add('active'); // Activa el submenu (para que sea visible)
                    const parentHasSubmenu = parentSubmenu.closest('.has-submenu');
                    if (parentHasSubmenu) {
                        parentHasSubmenu.classList.add('active'); // Activa el li.has-submenu (para el efecto de hover/expansión)
                    }
                    currentElement = parentHasSubmenu; // Continúa subiendo desde el li padre
                } else {
                    currentElement = null; // Detener si no hay más submenús padres
                }
            }
        });

        // Manejo del redimensionamiento para resetear el estado del menú móvil/desktop
        let isMobileView = window.matchMedia("(max-width: 900px)").matches;
        window.addEventListener('resize', function() {
            const newIsMobileView = window.matchMedia("(max-width: 900px)").matches;
            if (newIsMobileView !== isMobileView) {
                // Si la vista cambia (de móvil a desktop o viceversa)
                isMobileView = newIsMobileView;
                if (!isMobileView) {
                    // Si estamos en desktop, asegúrate de que el menú móvil no esté forzado a abierto
                    mainNav.classList.remove('active');
                    // Y cierra todos los submenús desplegables que pudieran estar abiertos en modo móvil
                    document.querySelectorAll('.has-submenu.active').forEach(parentLi => {
                        parentLi.classList.remove('active');
                    });
                }
            }
        });
    });
</script>