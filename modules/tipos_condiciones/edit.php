<?php
// modules/tipos_condiciones/edit.php

require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/db_connection.php';

// Determina la ruta base del proyecto para enlaces
$root_path = '/';
$project_folder_name = 'SISOA'; // **AJUSTA ESTO AL NOMBRE REAL DE TU CARPETA DE PROYECTO**
if (strpos($_SERVER['SCRIPT_NAME'], '/' . $project_folder_name . '/') !== false) {
    $root_path = '/' . $project_folder_name . '/';
}
$root_path = rtrim($root_path, '/') . '/'; // Asegura la barra final

// Asegúrate de que solo los administradores puedan acceder
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    header("Location: " . $root_path . "dashboard.php");
    exit();
}

$id_tipo_condicion = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo_condicion = null;
$message = '';
$message_type = '';

if ($id_tipo_condicion === 0) {
    $_SESSION['message'] = "ID de tipo de condición no especificado.";
    $_SESSION['message_type'] = "error";
    header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id_tipo_condicion, nombre_tipo, descripcion, activo FROM tipos_condiciones WHERE id_tipo_condicion = ?");
    $stmt->bind_param("i", $id_tipo_condicion);
    $stmt->execute();
    $result = $stmt->get_result();
    $tipo_condicion = $result->fetch_assoc();
    $stmt->close();

    if (!$tipo_condicion) {
        $_SESSION['message'] = "Tipo de condición no encontrado.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
        exit();
    }
} catch (Exception $e) {
    error_log("Error al cargar tipo de condición para edición: " . $e->getMessage());
    $_SESSION['message'] = "Error al cargar los datos del tipo de condición. Por favor, inténtelo de nuevo más tarde.";
    $_SESSION['message_type'] = "error";
    header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
    exit();
}

$conn->close();

// Obtener la URI actual para marcar el menú activo en main_header.php
$current_uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tipo de Condición - Ferrominera SST</title>
    <link rel="stylesheet" href="<?php echo $root_path; ?>css/main.css">
    <link rel="icon" href="<?php echo $root_path; ?>img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/main_header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Editar Tipo de Condición</h1>

            <?php if (!empty($message)): ?>
                <div class="notification-message <?php echo $message_type; ?> show">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form action="<?php echo $root_path; ?>modules/tipos_condiciones/process.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_tipo_condicion" value="<?php echo htmlspecialchars($tipo_condicion['id_tipo_condicion']); ?>">

                    <div class="form-group">
                        <label for="nombre_tipo">Nombre del Tipo de Condición:</label>
                        <input type="text" id="nombre_tipo" name="nombre_tipo" value="<?php echo htmlspecialchars($tipo_condicion['nombre_tipo']); ?>" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" rows="4" maxlength="500"><?php echo htmlspecialchars($tipo_condicion['descripcion']); ?></textarea>
                    </div>

                    <div class="form-group checkbox-group">
                        <label class="checkbox-container">Activo
                            <input type="checkbox" name="activo" value="1" <?php echo $tipo_condicion['activo'] ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17 3H7c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 16H8V6h8v13zM9 10h6V8H9v2zm0 4h6v-2H9v2zm0 4h4v-2H9v2z"/></svg>
                            <span>Actualizar</span>
                        </button>
                        <a href="<?php echo $root_path; ?>modules/tipos_condiciones/index.php" class="btn btn-secondary">
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