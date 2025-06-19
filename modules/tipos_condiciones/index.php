<?php
// modules/tipos_condiciones/index.php

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

$tipos_condiciones = [];
$message = '';
$message_type = '';

// Lógica para mostrar mensajes después de una operación
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

try {
    $stmt = $conn->prepare("SELECT id_tipo_condicion, nombre_tipo, descripcion, activo, creado_en, actualizado_en FROM tipos_condiciones ORDER BY nombre_tipo ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $tipos_condiciones[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al cargar tipos de condiciones: " . $e->getMessage());
    $message = "Error al cargar los tipos de condiciones. Por favor, inténtelo de nuevo más tarde.";
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
    <title>Tipos de Condiciones - Ferrominera SST</title>
    <link rel="stylesheet" href="<?php echo $root_path; ?>css/main.css">
    <link rel="icon" href="<?php echo $root_path; ?>img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/main_header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Gestión de Tipos de Condiciones</h1>

            <?php if (!empty($message)): ?>
                <div class="notification-message <?php echo $message_type; ?> show">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="actions-bar">
                <a href="<?php echo $root_path; ?>modules/tipos_condiciones/create.php" class="btn btn-primary">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm5 9h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                    <span>Crear Nuevo Tipo</span>
                </a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo de Condición</th>
                            <th>Descripción</th>
                            <th>Activo</th>
                            <th>Creado En</th>
                            <th>Actualizado En</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tipos_condiciones)): ?>
                            <?php foreach ($tipos_condiciones as $tipo): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($tipo['id_tipo_condicion']); ?></td>
                                    <td><?php echo htmlspecialchars($tipo['nombre_tipo']); ?></td>
                                    <td><?php echo htmlspecialchars($tipo['descripcion']); ?></td>
                                    <td>
                                        <?php if ($tipo['activo']): ?>
                                            <span class="status-active">Sí</span>
                                        <?php else: ?>
                                            <span class="status-inactive">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($tipo['creado_en']); ?></td>
                                    <td><?php echo htmlspecialchars($tipo['actualizado_en']); ?></td>
                                    <td class="actions">
                                        <a href="<?php echo $root_path; ?>modules/tipos_condiciones/edit.php?id=<?php echo htmlspecialchars($tipo['id_tipo_condicion']); ?>" class="btn btn-sm btn-edit" title="Editar">
                                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L18.25 9.75l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <form action="<?php echo $root_path; ?>modules/tipos_condiciones/process.php" method="POST" style="display: inline-block;" onsubmit="return confirm('¿Está seguro de que desea eliminar este tipo de condición? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_tipo_condicion" value="<?php echo htmlspecialchars($tipo['id_tipo_condicion']); ?>">
                                            <button type="submit" class="btn btn-sm btn-delete" title="Eliminar">
                                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No hay tipos de condiciones registrados.</td>
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