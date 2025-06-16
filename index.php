<?php
// index.php - Página de inicio de sesión

// **Para depuración, puedes poner esto aquí temporalmente, pero quítalo en producción**
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Siempre inicia la sesión al principio de cualquier script que la use.
// Si ya la iniciaste en otro lado y este es un archivo de inclusión que se carga después,
// podrías ver el "Notice: session_start(): Ignoring session_start()".
// Sin embargo, para la página de login, es común que sea el primer script en iniciarla.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db_connection.php'; // Incluye la conexión a la base de datos

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_input = trim($_POST['username'] ?? '');
    $password_input = $_POST['password'] ?? '';

    if (empty($username_input) || empty($password_input)) {
        $message = 'Por favor, ingrese su usuario y contraseña.';
    } else {
        // **IMPORTANTE: SELECCIONAR LA COLUMNA 'rol' DE LA BASE DE DATOS**
        $stmt = $conn->prepare("SELECT id_usuario, username, password, rol FROM usuarios WHERE username = ? AND activo = 1");
        if ($stmt === false) {
            $message = 'Error en la preparación de la consulta: ' . $conn->error;
        } else {
            $stmt->bind_param("s", $username_input);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                // Verificar la contraseña hasheada
                if (password_verify($password_input, $user['password'])) {
                    // Inicio de sesión exitoso
                    $_SESSION['loggedin'] = true;
                    $_SESSION['id_usuario'] = $user['id_usuario'];
                    $_SESSION['username'] = $user['username'];
                    // **ASIGNAR EL PERFIL BASADO EN LA COLUMNA 'rol' DE LA DB**
                    $_SESSION['perfiles'] = [$user['rol']]; // Suponiendo un solo rol por usuario

                    // Redirigir al dashboard
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $message = 'Usuario o contraseña incorrectos.';
                }
            } else {
                $message = 'Usuario o contraseña incorrectos.';
            }
            $stmt->close();
        }
    }
    $conn->close();
}

// Mensajes de sesión (ej. si viene de logout)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Ferrominera SST</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <img src="img/logo.png" alt="Logo Ferrominera SST" class="login-logo">
            <h1>Iniciar Sesión</h1>
        </div>
        <form action="index.php" method="POST" class="login-form">
            <?php if ($message): ?>
                <p class="error-message"><?php echo $message; ?></p>
            <?php endif; ?>
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>
    </div>
</body>
</html>