<?php
// modules/centros_trabajo/create.php

require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/db_connection.php';

// Asegúrate de que solo los administradores puedan acceder
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    header("Location: " . $root_path . "dashboard.php");
    exit();
}

$message = '';
$message_type = '';

// Obtener la URI actual para marcar el menú activo en main_header.php
$current_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Determina la ruta base del proyecto para enlaces (ej. /SISOA/ o /)
$root_path = '/';
$project_folder_name = 'SISOA'; // **AJUSTA ESTO AL NOMBRE REAL DE TU CARPETA DE PROYECTO**
if (strpos($_SERVER['SCRIPT_NAME'], '/' . $project_folder_name . '/') !== false) {
    $root_path = '/' . $project_folder_name . '/';
}
$root_path = rtrim($root_path, '/') . '/'; // Asegura la barra final
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Centro de Trabajo - Ferrominera SST</title>
    <link rel="stylesheet" href="<?php echo $root_path; ?>css/main.css">
    <link rel="icon" href="<?php echo $root_path; ?>img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php
    // Incluye el header principal con el menú de navegación
    require_once __DIR__ . '/../../includes/main_header.php';
    ?>

    <main class="content">
        <div class="container">
            <h1>Crear Nuevo Centro de Trabajo</h1>

            <?php if (!empty($message)): ?>
                <div class="notification-message <?php echo $message_type; ?> show">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="<?php echo $root_path; ?>modules/centros_trabajo/process.php" method="POST">
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label for="codigo_centro">Código del Centro:</label>
                        <input type="text" id="codigo_centro" name="codigo_centro" class="uppercase-input" required maxlength="20">
                    </div>

                    <div class="form-group">
                        <label for="nombre_centro">Nombre del Centro:</label>
                        <input type="text" id="nombre_centro" name="nombre_centro" required maxlength="100">
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-container">Activo
                            <input type="checkbox" name="activo" value="1" checked>
                            <span class="checkmark"></span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm5 9h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                            <span>Guardar</span>
                        </button>
                        <a href="<?php echo $root_path; ?>modules/centros_trabajo/index.php" class="btn btn-secondary">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                            <span>Cancelar</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="<?php echo $root_path; ?>js/main.js"></script>
</body>
</html>