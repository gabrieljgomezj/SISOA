<?php
// modules/gerencias/edit_gerencia.php - Formulario para editar gerencia existente

require_once __DIR__ . '/../../includes/session_check.php';
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = 'No tienes permiso para acceder a esta sección.';
    header('Location: ' . '../../dashboard.php');
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';

$gerencia_id = $_GET['id'] ?? null;
$message = '';
$message_type = '';
$gerencia_data = null; // Para almacenar los datos de la gerencia si se encuentra

// Cargar datos de la gerencia si existe un ID
if ($gerencia_id) {
    $stmt = $conn->prepare("SELECT id_gerencia, codigo_gerencia, nombre_gerencia, activo FROM gerencias WHERE id_gerencia = ?");
    $stmt->bind_param("i", $gerencia_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $gerencia_data = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = 'Gerencia no encontrada.';
        header('Location: index.php');
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['message'] = 'ID de gerencia no especificado.';
    header('Location: index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_gerencia = trim($_POST['codigo_gerencia'] ?? '');
    $nombre_gerencia = trim($_POST['nombre_gerencia'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validación
    if (empty($codigo_gerencia) || empty($nombre_gerencia)) {
        $message = 'El código y el nombre de la gerencia son obligatorios.';
        $message_type = 'error';
    } else {
        // Verificar si el código o nombre de gerencia ya existe para OTRA gerencia
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM gerencias WHERE (codigo_gerencia = ? OR nombre_gerencia = ?) AND id_gerencia != ?");
        $stmt_check->bind_param("ssi", $codigo_gerencia, $nombre_gerencia, $gerencia_id);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $message = 'Ya existe otra gerencia con ese código o nombre. Por favor, ingrese valores únicos.';
            $message_type = 'error';
        } else {
            $sql = "UPDATE gerencias SET codigo_gerencia = ?, nombre_gerencia = ?, activo = ? WHERE id_gerencia = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $codigo_gerencia, $nombre_gerencia, $activo, $gerencia_id);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Gerencia "' . htmlspecialchars($nombre_gerencia) . '" actualizada exitosamente.';
                header('Location: index.php');
                exit();
            } else {
                $message = 'Error al actualizar la gerencia: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Gerencia - Ferrominera SST</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/main_header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Editar Gerencia: <?php echo htmlspecialchars($gerencia_data['nombre_gerencia'] ?? ''); ?></h1>

            <?php if ($message): ?>
                <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="edit_gerencia.php?id=<?php echo htmlspecialchars($gerencia_id); ?>" method="POST" class="form-card">
                <div class="form-group">
                    <label for="codigo_gerencia">Código de Gerencia:</label>
                    <input type="text" id="codigo_gerencia" name="codigo_gerencia" value="<?php echo htmlspecialchars($gerencia_data['codigo_gerencia'] ?? ''); ?>" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="nombre_gerencia">Nombre de Gerencia:</label>
                    <input type="text" id="nombre_gerencia" name="nombre_gerencia" value="<?php echo htmlspecialchars($gerencia_data['nombre_gerencia'] ?? ''); ?>" required maxlength="100">
                </div>
                <div class="form-group checkbox-group-single">
                    <label>
                        <input type="checkbox" name="activo" value="1" <?php echo (isset($gerencia_data['activo']) && $gerencia_data['activo']) ? 'checked' : ''; ?>> Activa
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php // require_once __DIR__ . '/../../includes/footer.php'; ?>
    <script src="../../js/main.js"></script>
</body>
</html>