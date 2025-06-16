<?php
// includes/session_check.php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['message'] = 'Por favor, inicie sesión para acceder.';
    header('Location: ' . $root_path . 'index.php'); // Ajusta $root_path o la ruta aquí
    exit();
}
// Asegurarse de que perfiles siempre sea un array
if (!isset($_SESSION['perfiles']) || !is_array($_SESSION['perfiles'])) {
    $_SESSION['perfiles'] = [];
}
?>