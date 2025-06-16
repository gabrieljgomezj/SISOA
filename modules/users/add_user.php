<?php
// modules/users/add_user.php - Formulario para añadir nuevo usuario

require_once __DIR__ . '/../../includes/session_check.php';
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = 'No tienes permiso para acceder a esta sección.';
    header('Location: ' . '../../dashboard.php');
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';

$message = '';
$message_type = ''; // 'success' or 'error'

// Variables para prellenar el formulario en caso de error
$nombre = '';
$apellido = '';
$cedula = '';
$ficha = '';
$correo = '';
$username_form = ''; // Usar un nombre diferente para evitar conflicto con $_POST['username']
$activo = 1; // Por defecto activo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $ficha = trim($_POST['ficha'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $username_form = trim($_POST['username'] ?? ''); // Capturar el valor
    $password_input = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validación básica
    if (empty($nombre) || empty($apellido) || empty($cedula) || empty($correo) || empty($username_form) || empty($password_input) || empty($confirm_password)) {
        $message = 'Todos los campos obligatorios deben ser completados.';
        $message_type = 'error';
    } elseif ($password_input !== $confirm_password) {
        $message = 'La contraseña y la confirmación no coinciden.';
        $message_type = 'error';
    } elseif (strlen($password_input) < 6) { // Ejemplo: mínimo 6 caracteres
        $message = 'La contraseña debe tener al menos 6 caracteres.';
        $message_type = 'error';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $message = 'Por favor, ingrese un formato de correo electrónico válido.';
        $message_type = 'error';
    } else {
        // Verificar si el username, cédula o correo ya existen
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ? OR cedula = ? OR correo = ?");
        $stmt_check->bind_param("sss", $username_form, $cedula, $correo);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count > 0) {
            $message = 'El nombre de usuario, cédula o correo ya existe. Por favor, elija valores únicos.';
            $message_type = 'error';
        } else {
            // Hashear la contraseña
            $password_hash = password_hash($password_input, PASSWORD_DEFAULT);

            // **CORRECCIÓN: Insertar en las columnas correctas**
            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, cedula, ficha, correo, username, password, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssi", $nombre, $apellido, $cedula, $ficha, $correo, $username_form, $password_hash, $activo);

            if ($stmt->execute()) {
                $_SESSION['message'] = 'Usuario "' . htmlspecialchars($username_form) . '" añadido exitosamente.';
                header('Location: index.php');
                exit();
            } else {
                $message = 'Error al añadir el usuario: ' . $stmt->error;
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
    <title>Añadir Usuario - Ferrominera SST</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/main_header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Añadir Nuevo Usuario</h1>

            <?php if ($message): ?>
                <p class="message <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <form action="add_user.php" method="POST" class="form-card">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($apellido); ?>" required maxlength="100">
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($cedula); ?>" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="ficha">Ficha (opcional):</label>
                    <input type="text" id="ficha" name="ficha" value="<?php echo htmlspecialchars($ficha); ?>" maxlength="20">
                </div>
                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username_form); ?>" required maxlength="50">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group checkbox-group-single">
                    <label>
                        <input type="checkbox" name="activo" value="1" <?php echo $activo ? 'checked' : ''; ?>> Activo
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php // require_once __DIR__ . '/../../includes/footer.php'; ?>
    <script src="../../js/main.js"></script>
</body>
</html>