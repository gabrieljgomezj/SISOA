<?php
/**
 * logout.php
 *
 * Script para cerrar la sesión activa del usuario.
 * Destruye todas las variables de sesión y redirige al usuario a la página de login.
 */

// Inicia la sesión. Es crucial llamar a session_start() antes de cualquier salida al navegador.
session_start();

// Destruye todas las variables de sesión.
$_SESSION = array();

// Si se desea destruir la sesión completamente, también se debe destruir la cookie de sesión.
// Nota: Esto destruirá la sesión, y no solo los datos de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruye la sesión.
session_destroy();

// Redirige al usuario a la página de login.
header("location: login.php");
exit; // Asegura que el script se detenga después de la redirección.
?>