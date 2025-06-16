<?php
/**
 * includes/main_header.php
 *
 * Este archivo contiene el código HTML y PHP para el encabezado principal
 * y el menú de navegación de la aplicación Ferrominera SST.
 *
 * Las opciones del menú se renderizan dinámicamente basándose en los perfiles
 * de usuario almacenados en la sesión.
 *
 * Adaptado para una estructura de menú simplificada a dos niveles.
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

// **CÓDIGO PARA DETERMINAR $root_path**
$root_path = '/';
$project_folder_name = 'SISOA'; // <--- **AJUSTA ESTO AL NOMBRE REAL DE TU CARPETA DE PROYECTO**
if (strpos($_SERVER['SCRIPT_NAME'], '/' . $project_folder_name . '/') !== false) {
    $root_path = '/' . $project_folder_name . '/';
}

// Obtener la URI actual para marcar el menú activo
$current_uri = $_SERVER['REQUEST_URI'];
// Quitar el query string si existe para una comparación limpia
$current_uri_path = strtok($current_uri, '?');

// Función auxiliar para determinar si una URI actual coincide con una ruta de módulo.
// Ahora más precisa para evitar activaciones no deseadas.
function is_active_module($current_uri_path, $module_relative_path) {
    global $root_path; // Acceder a la variable global root_path
    $full_module_path = $root_path . 'modules/' . $module_relative_path;

    // Verifica si la URI actual termina con o es exactamente la ruta del módulo.
    // Esto es crucial para diferenciar entre /SISOA/dashboard.php y /SISOA/modules/users/index.php
    // Usamos rtrim para manejar casos donde $full_module_path podría terminar en '/'
    return strpos($current_uri_path, rtrim($full_module_path, '/')) !== false;
}

// Determinar si la página actual es el dashboard
$is_dashboard_active = (basename($_SERVER['PHP_SELF']) == 'dashboard.php');

?>

<header class="main-header">
    <nav class="main-nav">
        <div class="nav-brand">
            <a href="<?php echo $root_path; ?>dashboard.php">
                <img src="<?php echo $root_path; ?>img/logo.png" alt="Logo Ferrominera SST">
            </a>
        </div>
        <ul class="nav-menu">
            <li class="nav-item <?php echo $is_dashboard_active ? 'active' : ''; ?>">
                <a href="<?php echo $root_path; ?>dashboard.php" class="nav-link">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                    <span>Dashboard</span>
                </a>
            </li>

            <?php if ($isAdmin): ?>
                <?php
                // Determina si alguna de las sub-opciones del panel de administración está activa.
                // Usamos la versión de $current_uri_path limpia.
                $admin_panel_sub_active = is_active_module($current_uri_path, 'users') ||
                                          is_active_module($current_uri_path, 'gerencias') ||
                                          is_active_module($current_uri_path, 'centros_trabajo') ||
                                          is_active_module($current_uri_path, 'tipos_condiciones');

                // El menú "Panel de Administración" estará activo si alguna de sus sub-páginas está activa,
                // PERO NO si la página actual es el dashboard.
                $admin_menu_parent_active = $admin_panel_sub_active && !$is_dashboard_active;
                ?>
                <li class="nav-item has-submenu <?php echo $admin_menu_parent_active ? 'active' : ''; ?>">
                    <a href="#" class="nav-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6-4v12c0 1.1-.9 2-2 2H8c-1.1 0-2-.9-2-2V8c0-1.1.9-2 2-2h8c1.1 0 2 .9 2 2zm-4 0H8v12h8V8zM5 4h14V2H5v2z"/></svg>
                        <span>Panel de Administración</span>
                    </a>
                    <ul class="submenu <?php echo $admin_menu_parent_active ? 'active' : ''; ?>">
                        <li>
                            <a href="<?php echo $root_path; ?>modules/users/index.php" class="<?php echo is_active_module($current_uri_path, 'users') ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.38 0 2.5 1.12 2.5 2.5S13.38 10 12 10 9.5 8.88 9.5 7.5 10.62 5 12 5zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.88 6-3.88s5.97 1.89 6 3.88c-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                                <span>Gestión de Usuarios</span>
                            </a>
                        </li>

                        <li>
                            <a href="<?php echo $root_path; ?>modules/gerencias/index.php" class="<?php echo is_active_module($current_uri_path, 'gerencias') ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                                <span>Gerencias</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $root_path; ?>modules/centros_trabajo/index.php" class="<?php echo is_active_module($current_uri_path, 'centros_trabajo') ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                                <span>Centros de Trabajo</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo $root_path; ?>modules/tipos_condiciones/index.php" class="<?php echo is_active_module($current_uri_path, 'tipos_condiciones') ? 'active' : ''; ?>">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.38 0 2.5 1.12 2.5 2.5S13.38 10 12 10 9.5 8.88 9.5 7.5 10.62 5 12 5zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.88 6-3.88s5.97 1.89 6 3.88c-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                                <span>Tipos de Condiciones</span>
                            </a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($isAdmin || $isOperador): ?>
                <?php
                $gestion_eventos_active = is_active_module($current_uri_path, 'condiciones_inseguras') ||
                                          is_active_module($current_uri_path, 'accidentes_trabajo');
                ?>
                <li class="nav-item has-submenu <?php echo $gestion_eventos_active ? 'active' : ''; ?>">
                    <a href="#" class="nav-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                        <span>Gestión de Eventos SST</span>
                    </a>
                    <ul class="submenu <?php echo $gestion_eventos_active ? 'active' : ''; ?>">
                        <li><a href="<?php echo $root_path; ?>modules/condiciones_inseguras/index.php" class="<?php echo is_active_module($current_uri_path, 'condiciones_inseguras') ? 'active' : ''; ?>">Condiciones Inseguras</a></li>
                        <li><a href="<?php echo $root_path; ?>modules/accidentes_trabajo/index.php" class="<?php echo is_active_module($current_uri_path, 'accidentes_trabajo') ? 'active' : ''; ?>">Accidentes de Trabajo</a></li>
                    </ul>
                </li>

                <?php
                $capacitacion_active = is_active_module($current_uri_path, 'formacion') ||
                                       is_active_module($current_uri_path, 'planes_trabajo');
                ?>
                <li class="nav-item has-submenu <?php echo $capacitacion_active ? 'active' : ''; ?>">
                    <a href="#" class="nav-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <span>Capacitación y Planificación</span>
                    </a>
                    <ul class="submenu <?php echo $capacitacion_active ? 'active' : ''; ?>">
                        <li><a href="<?php echo $root_path; ?>modules/formacion/index.php" class="<?php echo is_active_module($current_uri_path, 'formacion') ? 'active' : ''; ?>">Gestión de Formación</a></li>
                        <li><a href="<?php echo $root_path; ?>modules/planes_trabajo/index.php" class="<?php echo is_active_module($current_uri_path, 'planes_trabajo') ? 'active' : ''; ?>">Planes de Trabajo</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <?php if ($isAdmin || $isOperador || $isConsultor): ?>
                <?php
                $reportes_active = is_active_module($current_uri_path, 'reportes_condiciones') ||
                                   is_active_module($current_uri_path, 'reportes_accidentes');
                ?>
                <li class="nav-item has-submenu <?php echo $reportes_active ? 'active' : ''; ?>">
                    <a href="#" class="nav-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"/></svg>
                        <span>Reportes y Estadísticas</span>
                    </a>
                    <ul class="submenu <?php echo $reportes_active ? 'active' : ''; ?>">
                        <li><a href="<?php echo $root_path; ?>modules/reportes_condiciones/index.php" class="<?php echo is_active_module($current_uri_path, 'reportes_condiciones') ? 'active' : ''; ?>">Reporte Condiciones Inseguras</a></li>
                        <li><a href="<?php echo $root_path; ?>modules/reportes_accidentes/index.php" class="<?php echo is_active_module($current_uri_path, 'reportes_accidentes') ? 'active' : ''; ?>">Reporte Accidentes de Trabajo</a></li>
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