<?php
/**
 * db_connection.php
 *
 * Archivo para establecer la conexión a la base de datos MySQL.
 * Configura las credenciales y maneja posibles errores de conexión.
 */

// --- Configuración de la Base de Datos ---
// Define las credenciales para la conexión a la base de datos.
// Es crucial que estas credenciales sean seguras y no se expongan públicamente.
// Para entornos de producción, considera usar variables de entorno.
define('DB_SERVER', 'localhost'); // La dirección del servidor de la base de datos. Usualmente 'localhost' para desarrollo.
define('DB_USERNAME', 'seguridad_user');    // Tu nombre de usuario de la base de datos.
define('DB_PASSWORD', 'alfacanis6');        // Tu contraseña de la base de datos. ¡Debe ser una contraseña segura en producción!
define('DB_NAME', 'db_ferrominera_sst'); // El nombre de la base de datos que creamos.

// --- Conexión a la Base de Datos ---
// Intenta establecer una nueva conexión MySQLi.
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// --- Verificación de la Conexión ---
// Comprueba si hubo algún error durante el intento de conexión.
if ($conn->connect_error) {
    // Si la conexión falla, detiene la ejecución del script y muestra un mensaje de error.
    // En un entorno de producción, este mensaje debería ser más genérico para evitar
    // dar información sensible sobre la base de datos.
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// --- Configuración del Juego de Caracteres ---
// Establece el juego de caracteres a UTF-8 para asegurar el correcto manejo de caracteres especiales (ñ, tildes, etc.).
$conn->set_charset("utf8mb4");

// Opcional: Para depuración, puedes descomentar la siguiente línea para confirmar una conexión exitosa.
// echo "Conexión a la base de datos exitosa.";

?>