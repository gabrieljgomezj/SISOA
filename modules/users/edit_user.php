<?php
// modules/users/edit_user.php - Formulario para editar usuario existente

require_once __DIR__ . '/../../includes/session_check.php';
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = 'No tienes permiso para acceder a esta sección.';
    header('Location: ' . '../../dashboard.php');
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';

$user_id = $_GET['id'] ?? null;
$message = '';
$message_type = '';
$user_data = null; // Para almacenar los datos del usuario si se encuentra

// Cargar datos del usuario si existe un ID
if ($user_id) {
    // **CORRECCIÓN: Seleccionar las columnas correctas**
    $stmt = $conn->prepare("SELECT id_usuario, nombre, apellido, cedula, ficha, correo, username, activo FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user_data = $result->fetch_assoc();
        // **NOTA: No hay columna 'perfiles', así que no se decodifica JSON**
        // $user_data['perfiles'] = json_decode($user_data['perfiles'], true) ?? [];
    } else {
        $_SESSION['message'] = 'Usuario no encontrado.';
        header('Location: index.php');
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['message'] = 'ID de usuario no especificado.';
    header('Location: index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $ficha = trim($_POST['ficha'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $username_form = trim($_POST['username'] ?? ''); // Capturar el valor
    $password_input = $_POST['password'] ?? ''; // Opcional: si se quiere cambiar la contraseña
    $confirm_password = $_POST['confirm_password'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validación básica
    if (empty($nombre) || empty($apellido) || empty($cedula) || empty($correo) || empty($username_form)) {
        $message = 'Todos los campos obligatorios (nombre, apellido, cédula, correo, usuario) deben ser completados.';
        $message_type = 'error';
    } elseif (!empty($password_input) && $password_input !== $confirm_password) {
        $message = 'La nueva contraseña y su confirmación no coinciden.';
        $message_type = 'error';
    } elseif (!empty($password_input) && strlen($password_input) < 6) {
        $message = 'La nueva contraseña debe tener al menos 6 caracteres.';
        $message_type = 'error';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $message = 'Por favor, ingrese un formato de correo electrónico válido.';
        $message_type = 'error';
    } else {
        // Verificar si el username, cédula o correo ya existen para OTRO usuario
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE (username = ? OR cedula = ? OR correo = ?) AND id_usuario != ?");
        $stmt_check->bind_param("sssi", $username_form, $cedula, $correo, $user_id);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $message = 'El nombre de usuario, cédula o correo ya existe para otro usuario. Por favor, elija valores únicos.';
            $message_type = 'error';
        } else {
            $update_password_sql = '';
            $bind_params = [];
            $bind_types = '';

            // Construir la consulta de actualización dinámicamente
            $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, cedula = ?, ficha = ?, correo = ?, username = ?, activo = ?";
            $bind_params[] = $nombre;
            $bind_params[] = $apellido;
            $bind_params[] = $cedula;
            $bind_params[] = empty($ficha) ? null : $ficha; // Ficha puede ser NULL
            $bind_params[] = $correo;
            $bind_params[] = $username_form;
            $bind_params[] = $activo;
            $bind_types .= 'ssssssi';

            if (!empty($password_input)) {
                $password_hash = password_hash($password_input, PASSWORD_DEFAULT);
                $sql .= ", password = ?"; // **CORRECCIÓN: 'password_hash' por 'password'**
                $bind_params[] = $password_hash;
                $bind_types .= 's';
            }

            $sql .= " WHERE id_usuario = ?";
            $bind_params[] = $user_id;
            $bind_types .= 'i';

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($bind_types, ...$bind_params);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Usuario "' . htmlspecialchars($username_form) . '" actualizado exitosamente.';
                header('Location: index.php');
                exit();
            } else {
                $message = 'Error al actualizar el usuario: ' . $stmt->error;
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
    <title>Editar Usuario - Ferrominera SST</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/main_header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Editar Usuario: <?php echo htmlspecialchars($user_data['username'] ?? ''); ?></h1> <?php if ($message): ?>
                <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="edit_user.php?id=<?php echo htmlspecialchars($user_id); ?>" method="POST" class="form-card">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user_data['nombre'] ?? ''); ?>" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user_data['apellido'] ?? ''); ?>" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($user_data['cedula'] ?? ''); ?>" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="ficha">Ficha (opcional):</label>
                    <input type="text" id="ficha" name="ficha" value="<?php echo htmlspecialchars($user_data['ficha'] ?? ''); ?>" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($user_data['correo'] ?? ''); ?>" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="password">Nueva Contraseña (dejar en blanco para no cambiar):</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <div class="form-group checkbox-group-single">
                    <label>
                        <input type="checkbox" name="activo" value="1" <?php echo (isset($user_data['activo']) && $user_data['activo']) ? 'checked' : ''; ?>> Activo
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