<?php
// logout.php - Cierra la sesión del usuario

session_start();

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la cookie de sesión, se debe borrar también.
// Nota: Esto destruirá la sesión, y no solo los datos de la sesión!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Mensaje de éxito al cerrar sesión
$_SESSION['message'] = 'Ha cerrado su sesión exitosamente.';

// Redirigir al inicio de sesión
header("Location: index.php");
exit();
?>