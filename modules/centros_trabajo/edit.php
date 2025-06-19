<?php
// modules/centros_trabajo/edit.php

require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/db_connection.php';

// Asegúrate de que solo los administradores puedan acceder
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    header("Location: " . $root_path . "dashboard.php");
    exit();
}

$centro_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$centro = null;
$message = '';
$message_type = '';

if ($centro_id === 0) {
    $_SESSION['message'] = "ID de centro de trabajo no especificado.";
    $_SESSION['message_type'] = "error";
    header("Location: " . $root_path . "modules/centros_trabajo/index.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id_centro, codigo_centro, nombre_centro, activo FROM centros_trabajo WHERE id_centro = ?");
    $stmt->bind_param("i", $centro_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $centro = $result->fetch_assoc();
    $stmt->close();

    if (!$centro) {
        $_SESSION['message'] = "Centro de trabajo no encontrado.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $root_path . "modules/centros_trabajo/index.php");
        exit();
    }
} catch (Exception $e) {
    error_log("Error al cargar centro de trabajo para edición: " . $e->getMessage());
    $_SESSION['message'] = "Error al cargar los datos del centro de trabajo. Por favor, inténtelo de nuevo más tarde.";
    $_SESSION['message_type'] = "error";
    header("Location: " . $root_path . "modules/centros_trabajo/index.php");
    exit();
}

$conn->close();

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
    <title>Editar Centro de Trabajo - Ferrominera SST</title>
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
            <h1>Editar Centro de Trabajo</h1>

            <?php if (!empty($message)): ?>
                <div class="notification-message <?php echo $message_type; ?> show">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="<?php echo $root_path; ?>modules/centros_trabajo/process.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_centro" value="<?php echo htmlspecialchars($centro['id_centro']); ?>">

                    <div class="form-group">
                        <label for="codigo_centro">Código del Centro:</label>
                        <input type="text" id="codigo_centro" name="codigo_centro" class="uppercase-input" value="<?php echo htmlspecialchars($centro['codigo_centro']); ?>" required maxlength="20">
                    </div>

                    <div class="form-group">
                        <label for="nombre_centro">Nombre del Centro:</label>
                        <input type="text" id="nombre_centro" name="nombre_centro" value="<?php echo htmlspecialchars($centro['nombre_centro']); ?>" required maxlength="100">
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-container">Activo
                            <input type="checkbox" name="activo" value="1" <?php echo $centro['activo'] ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 16H8V6h8v13zM9 10h6V8H9v2zm0 4h6v-2H9v2zm0 4h4v-2H9v2z"/></svg>
                            <span>Actualizar</span>
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