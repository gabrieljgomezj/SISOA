<?php
// modules/centros_trabajo/index.php

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

$centros_trabajo = [];
$message = '';
$message_type = '';

// Lógica para mostrar mensajes después de una operación (ej. creado, actualizado, eliminado)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']); // Eliminar el mensaje de la sesión para que no se muestre de nuevo
    unset($_SESSION['message_type']); // Eliminar el tipo de mensaje
}

try {
    $stmt = $conn->prepare("SELECT id_centro, codigo_centro, nombre_centro, activo, creado_en, actualizado_en FROM centros_trabajo ORDER BY nombre_centro ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $centros_trabajo[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al cargar centros de trabajo: " . $e->getMessage());
    $message = "Error al cargar los centros de trabajo. Por favor, inténtelo de nuevo más tarde.";
    $message_type = "error";
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
    <title>Centros de Trabajo - Ferrominera SST</title>
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
            <h1>Gestión de Centros de Trabajo</h1>

            <?php if (!empty($message)): ?>
                <div class="notification-message <?php echo $message_type; ?> show">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="actions-bar">
                <a href="<?php echo $root_path; ?>modules/centros_trabajo/create.php" class="btn btn-primary">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm5 9h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                    <span>Crear Nuevo Centro</span>
                </a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Activo</th>
                            <th>Creado En</th>
                            <th>Actualizado En</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($centros_trabajo)): ?>
                            <?php foreach ($centros_trabajo as $centro): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($centro['id_centro']); ?></td>
                                    <td><?php echo htmlspecialchars($centro['codigo_centro']); ?></td>
                                    <td><?php echo htmlspecialchars($centro['nombre_centro']); ?></td>
                                    <td>
                                        <?php if ($centro['activo']): ?>
                                            <span class="status-active">Sí</span>
                                        <?php else: ?>
                                            <span class="status-inactive">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($centro['creado_en']); ?></td>
                                    <td><?php echo htmlspecialchars($centro['actualizado_en']); ?></td>
                                    <td class="actions">
                                        <a href="<?php echo $root_path; ?>modules/centros_trabajo/edit.php?id=<?php echo htmlspecialchars($centro['id_centro']); ?>" class="btn btn-sm btn-edit" title="Editar">
                                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L18.25 9.75l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <form action="<?php echo $root_path; ?>modules/centros_trabajo/process.php" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de que desea eliminar este centro de trabajo? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_centro" value="<?php echo htmlspecialchars($centro['id_centro']); ?>">
                                            <button type="submit" class="btn btn-sm btn-delete" title="Eliminar">
                                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay centros de trabajo registrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="<?php echo $root_path; ?>js/main.js"></script>
</body>
</html>