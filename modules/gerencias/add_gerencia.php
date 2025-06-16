<?php
// modules/gerencias/add_gerencia.php - Formulario para añadir nueva gerencia

require_once __DIR__ . '/../../includes/session_check.php';
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = 'No tienes permiso para acceder a esta sección.';
    header('Location: ' . '../../dashboard.php');
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';

$message = '';
$message_type = ''; // 'success' or 'error'
$codigo_gerencia = '';
$nombre_gerencia = '';
$activo = 1; // Por defecto activa

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo_gerencia = trim($_POST['codigo_gerencia'] ?? '');
    $nombre_gerencia = trim($_POST['nombre_gerencia'] ?? '');
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validación
    if (empty($codigo_gerencia) || empty($nombre_gerencia)) {
        $message = 'El código y el nombre de la gerencia son obligatorios.';
        $message_type = 'error';
    } else {
        // Verificar si el código o nombre de gerencia ya existe
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM gerencias WHERE codigo_gerencia = ? OR nombre_gerencia = ?");
        $stmt_check->bind_param("ss", $codigo_gerencia, $nombre_gerencia);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $message = 'Ya existe una gerencia con ese código o nombre. Por favor, ingrese valores únicos.';
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare("INSERT INTO gerencias (codigo_gerencia, nombre_gerencia, activo) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $codigo_gerencia, $nombre_gerencia, $activo);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Gerencia "' . htmlspecialchars($nombre_gerencia) . '" añadida exitosamente.';
                header('Location: index.php');
                exit();
            } else {
                $message = 'Error al añadir la gerencia: ' . $stmt->error;
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
    <title>Añadir Gerencia - Ferrominera SST</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/main_header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Añadir Nueva Gerencia</h1>

            <?php if ($message): ?>
                <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="add_gerencia.php" method="POST" class="form-card">
                <div class="form-group">
                    <label for="codigo_gerencia">Código de Gerencia:</label>
                    <input type="text" id="codigo_gerencia" name="codigo_gerencia" value="<?php echo htmlspecialchars($codigo_gerencia); ?>" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="nombre_gerencia">Nombre de Gerencia:</label>
                    <input type="text" id="nombre_gerencia" name="nombre_gerencia" value="<?php echo htmlspecialchars($nombre_gerencia); ?>" required maxlength="100">
                </div>
                <div class="form-group checkbox-group-single">
                    <label>
                        <input type="checkbox" name="activo" value="1" <?php echo $activo ? 'checked' : ''; ?>> Activa
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Gerencia</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php // require_once __DIR__ . '/../../includes/footer.php'; ?>
    <script src="../../js/main.js"></script>
</body>
</html>