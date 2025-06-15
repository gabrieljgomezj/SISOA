<?php
/**
 * login.php
 *
 * Página de inicio de sesión del aplicativo web Ferrominera SST.
 * Procesa las credenciales del usuario, valida contra la base de datos
 * y gestiona las sesiones de usuario.
 */

// Inicia la sesión PHP. Es fundamental para manejar la autenticación del usuario.
session_start();

// Verifica si el usuario ya está logueado. Si es así, lo redirige al dashboard.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: dashboard.php");
    exit;
}

// Incluye el archivo de conexión a la base de datos.
require_once 'includes/db_connection.php';

// Define variables e inicializa con valores vacíos.
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Procesa el formulario cuando se envía.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validación de entradas:
    // Verifica si el nombre de usuario está vacío.
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor, ingrese su nombre de usuario.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Verifica si la contraseña está vacía.
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 2. Validación de Credenciales si no hay errores de entrada:
    if (empty($username_err) && empty($password_err)) {
        // Prepara una sentencia SELECT para buscar el usuario.
        $sql = "SELECT id_usuario, username, password, activo FROM usuarios WHERE username = ?";

        if ($stmt = $conn->prepare($sql)) {
            // Vincula el parámetro 'username' a la sentencia preparada.
            $stmt->bind_param("s", $param_username);
            $param_username = $username;

            // Intenta ejecutar la sentencia preparada.
            if ($stmt->execute()) {
                // Almacena el resultado de la consulta.
                $stmt->store_result();

                // Verifica si el nombre de usuario existe y si está activo.
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id_usuario, $username, $hashed_password, $activo);
                    if ($stmt->fetch()) {
                        // Verifica si el usuario está activo.
                        if ($activo == 1) {
                            // Verifica la contraseña ingresada con el hash almacenado.
                            if (password_verify($password, $hashed_password)) {
                                // La contraseña es correcta, inicia una nueva sesión.
                                session_regenerate_id(); // Regenera el ID de sesión para seguridad.
                                $_SESSION['loggedin'] = true;
                                $_SESSION['id_usuario'] = $id_usuario;
                                $_SESSION['username'] = $username;

                                // --- Obtener Perfiles del Usuario ---
                                // Esto es importante para controlar los accesos al menú.
                                $sql_perfil = "SELECT p.nombre_perfil FROM usuario_perfil up JOIN perfiles p ON up.id_perfil = p.id_perfil WHERE up.id_usuario = ?";
                                if ($stmt_perfil = $conn->prepare($sql_perfil)) {
                                    $stmt_perfil->bind_param("i", $id_usuario);
                                    if ($stmt_perfil->execute()) {
                                        $result_perfil = $stmt_perfil->get_result();
                                        $perfiles = [];
                                        while ($row_perfil = $result_perfil->fetch_assoc()) {
                                            $perfiles[] = $row_perfil['nombre_perfil'];
                                        }
                                        $_SESSION['perfiles'] = $perfiles;
                                    }
                                    $stmt_perfil->close();
                                }

                                // Actualiza la fecha de último login.
                                $sql_update_login = "UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = ?";
                                if ($stmt_update = $conn->prepare($sql_update_login)) {
                                    $stmt_update->bind_param("i", $id_usuario);
                                    $stmt_update->execute();
                                    $stmt_update->close();
                                }

                                // Redirige al usuario al dashboard después del login exitoso.
                                header("location: dashboard.php");
                            } else {
                                // Contraseña incorrecta.
                                $login_err = "Usuario o contraseña inválidos.";
                            }
                        } else {
                            // Usuario inactivo.
                            $login_err = "Su cuenta está inactiva. Por favor, contacte al administrador.";
                        }
                    }
                } else {
                    // Nombre de usuario no encontrado.
                    $login_err = "Usuario o contraseña inválidos.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            // Cierra la sentencia preparada.
            $stmt->close();
        }
    }

    // Cierra la conexión a la base de datos al final del procesamiento del formulario.
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ferrominera SST</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-logo">
                <img src="img/logo.png" alt="Logo CVG Ferrominera Orinoco">
            </div>
            <h2>Bienvenido</h2>
            <p>Por favor, ingrese sus credenciales para iniciar sesión.</p>

            <?php
            // Muestra el mensaje de error de login si existe.
            if (!empty($login_err)) {
                echo '<div class="notification-message error" id="loginErrorNotification">' . $login_err . '</div>';
            }
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>" required>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>