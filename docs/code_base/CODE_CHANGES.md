# Registro de Cambios en el Código Frontend (main_header.php, main.js, main.css)

Este documento detalla las modificaciones y mejoras realizadas en los archivos clave del frontend (`main_header.php`, `main.js`, `main.css`) para optimizar el comportamiento del menú de navegación, especialmente en lo referente a la activación y desactivación de sus elementos.

## 1. Archivo Modificado: `/SISOA/includes/main_header.php`

**Propósito del Cambio:** Mejorar la lógica de detección de la página activa para el menú de navegación, asegurando que los elementos (padres y sub-elementos) se resalten correctamente y no se queden activos cuando no deben, particularmente el "Panel de Administración".

**Descripción de los Cambios:**

* **Refinamiento de `is_active_module`:**
    * La función `is_active_module($current_uri_path, $module_relative_path)` ahora es más precisa para determinar si la URI actual corresponde a un módulo específico.
    * Se utiliza `strtok($current_uri, '?')` para eliminar cualquier `query string` de la URL, permitiendo una comparación limpia de las rutas.
    * La comparación `strpos($current_uri_path, rtrim($full_module_path, '/')) !== false` asegura que la detección sea exacta, evitando activaciones falsas.

* **Control del menú "Panel de Administración":**
    * Se introdujo la variable `$is_dashboard_active` para identificar explícitamente si la página actual es `dashboard.php`.
    * La variable `$admin_menu_parent_active` ahora determina el estado `active` del `<li>` padre "Panel de Administración". Se activa **solo si** una de sus sub-páginas está activa **Y NO SE ESTÁ EN EL DASHBOARD**. Esto previene que el menú "Panel de Administración" se resalte innecesariamente en la página principal.

**Código Clave Relevante (Fragmentos):**

```php
<?php
// ... (código previo) ...

// Obtener la URI actual para marcar el menú activo
$current_uri = $_SERVER['REQUEST_URI'];
// Quitar el query string si existe para una comparación limpia
$current_uri_path = strtok($current_uri, '?');

// Función auxiliar para determinar si una URI actual coincide con una ruta de módulo.
function is_active_module($current_uri_path, $module_relative_path) {
    global $root_path;
    $full_module_path = $root_path . 'modules/' . $module_relative_path;
    return strpos($current_uri_path, rtrim($full_module_path, '/')) !== false;
}

// Determinar si la página actual es el dashboard
$is_dashboard_active = (basename($_SERVER['PHP_SELF']) == 'dashboard.php');

// ... (en la sección del Panel de Administración) ...
$admin_panel_sub_active = is_active_module($current_uri_path, 'users') ||
                          is_active_module($current_uri_path, 'gerencias') ||
                          is_active_module($current_uri_path, 'centros_trabajo') ||
                          is_active_module($current_uri_path, 'tipos_condiciones');

$admin_menu_parent_active = $admin_panel_sub_active && !$is_dashboard_active;
?>
<li class="nav-item has-submenu <?php echo $admin_menu_parent_active ? 'active' : ''; ?>">
    </li>