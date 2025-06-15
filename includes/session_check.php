<?php
/**
 * session_check.php
 *
 * Archivo de inclusión para verificar si un usuario ha iniciado sesión.
 * Si no hay una sesión activa, redirige al usuario a la página de login.
 */

// Verifica si la sesión 'loggedin' no está establecida o no es verdadera.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si el usuario no está logueado, redirigirlo a la página de login.
    header("location: login.php");
    exit; // Detiene la ejecución del script.
}
?>