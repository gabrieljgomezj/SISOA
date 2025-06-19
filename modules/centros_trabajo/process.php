<?php
// modules/centros_trabajo/process.php

require_once __DIR__ . '/../../includes/session_check.php';
require_once __DIR__ . '/../../includes/db_connection.php';

// Determina la ruta base del proyecto para redirecciones
$root_path = '/';
$project_folder_name = 'SISOA'; // **AJUSTA ESTO AL NOMBRE REAL DE TU CARPETA DE PROYECTO**
if (strpos($_SERVER['SCRIPT_NAME'], '/' . $project_folder_name . '/') !== false) {
    $root_path = '/' . $project_folder_name . '/';
}
$root_path = rtrim($root_path, '/') . '/'; // Asegura la barra final

// Asegúrate de que solo los administradores puedan acceder
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = "No tienes permiso para realizar esta acción.";
    $_SESSION['message_type'] = "error";
    header("Location: " . $root_path . "dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        switch ($action) {
            case 'create':
                // Validación básica de entrada
                $codigo_centro = trim($_POST['codigo_centro'] ?? '');
                $nombre_centro = trim($_POST['nombre_centro'] ?? '');
                $activo = isset($_POST['activo']) ? 1 : 0;

                if (empty($codigo_centro) || empty($nombre_centro)) {
                    $_SESSION['message'] = "El código y el nombre del centro de trabajo son obligatorios.";
                    $_SESSION['message_type'] = "error";
                    // Redirigir de vuelta a la página de creación con los datos para rellenar el formulario (opcional pero buena práctica)
                    $_SESSION['old_data'] = $_POST; // Guarda los datos enviados para rellenar
                    header("Location: " . $root_path . "modules/centros_trabajo/create.php");
                    exit();
                }

                // Convertir código a mayúsculas
                $codigo_centro = strtoupper($codigo_centro);

                // Verificar si el código o nombre ya existen
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM centros_trabajo WHERE codigo_centro = ? OR nombre_centro = ?");
                $stmt_check->bind_param("ss", $codigo_centro, $nombre_centro);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    $_SESSION['message'] = "Ya existe un centro de trabajo con el mismo código o nombre. Por favor, verifique.";
                    $_SESSION['message_type'] = "error";
                    $_SESSION['old_data'] = $_POST;
                    header("Location: " . $root_path . "modules/centros_trabajo/create.php");
                    exit();
                }

                $stmt = $conn->prepare("INSERT INTO centros_trabajo (codigo_centro, nombre_centro, activo) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $codigo_centro, $nombre_centro, $activo);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Centro de trabajo creado exitosamente.";
                    $_SESSION['message_type'] = "success";
                } else {
                    throw new Exception("Error al crear el centro de trabajo: " . $stmt->error);
                }
                $stmt->close();
                header("Location: " . $root_path . "modules/centros_trabajo/index.php");
                exit();

            case 'update':
                $id_centro = intval($_POST['id_centro'] ?? 0);
                $codigo_centro = trim($_POST['codigo_centro'] ?? '');
                $nombre_centro = trim($_POST['nombre_centro'] ?? '');
                $activo = isset($_POST['activo']) ? 1 : 0;

                if ($id_centro === 0 || empty($codigo_centro) || empty($nombre_centro)) {
                    $_SESSION['message'] = "Datos incompletos para actualizar el centro de trabajo.";
                    $_SESSION['message_type'] = "error";
                    // Si el ID es válido, redirigir de nuevo a la página de edición de ese ID
                    $redirect_url = ($id_centro !== 0) ? $root_path . "modules/centros_trabajo/edit.php?id=" . $id_centro : $root_path . "modules/centros_trabajo/index.php";
                    header("Location: " . $redirect_url);
                    exit();
                }

                $codigo_centro = strtoupper($codigo_centro);

                // Verificar si el código o nombre ya existen para OTRO centro (excluyendo el actual)
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM centros_trabajo WHERE (codigo_centro = ? OR nombre_centro = ?) AND id_centro != ?");
                $stmt_check->bind_param("ssi", $codigo_centro, $nombre_centro, $id_centro);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    $_SESSION['message'] = "Ya existe otro centro de trabajo con el mismo código o nombre. Por favor, verifique.";
                    $_SESSION['message_type'] = "error";
                    header("Location: " . $root_path . "modules/centros_trabajo/edit.php?id=" . $id_centro);
                    exit();
                }

                $stmt = $conn->prepare("UPDATE centros_trabajo SET codigo_centro = ?, nombre_centro = ?, activo = ? WHERE id_centro = ?");
                $stmt->bind_param("ssii", $codigo_centro, $nombre_centro, $activo, $id_centro);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Centro de trabajo actualizado exitosamente.";
                    $_SESSION['message_type'] = "success";
                } else {
                    throw new Exception("Error al actualizar el centro de trabajo: " . $stmt->error);
                }
                $stmt->close();
                header("Location: " . $root_path . "modules/centros_trabajo/index.php");
                exit();

            case 'delete':
                $id_centro = intval($_POST['id_centro'] ?? 0);

                if ($id_centro === 0) {
                    $_SESSION['message'] = "ID de centro de trabajo no especificado para eliminar.";
                    $_SESSION['message_type'] = "error";
                    header("Location: " . $root_path . "modules/centros_trabajo/index.php");
                    exit();
                }

                $stmt = $conn->prepare("DELETE FROM centros_trabajo WHERE id_centro = ?");
                $stmt->bind_param("i", $id_centro);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Centro de trabajo eliminado exitosamente.";
                    $_SESSION['message_type'] = "success";
                } else {
                    // Considerar si hay restricciones de clave foránea y dar un mensaje más específico
                    if ($conn->errno == 1451) { // Error de clave foránea
                        $_SESSION['message'] = "No se puede eliminar el centro de trabajo porque está siendo utilizado en otros registros (ej. usuarios).";
                        $_SESSION['message_type'] = "error";
                    } else {
                        throw new Exception("Error al eliminar el centro de trabajo: " . $stmt->error);
                    }
                }
                $stmt->close();
                header("Location: " . $root_path . "modules/centros_trabajo/index.php");
                exit();

            default:
                $_SESSION['message'] = "Acción no válida.";
                $_SESSION['message_type'] = "error";
                header("Location: " . $root_path . "dashboard.php");
                exit();
        }
    } catch (Exception $e) {
        error_log("Error en process.php (Centros de Trabajo): " . $e->getMessage());
        $_SESSION['message'] = "Ocurrió un error inesperado. Por favor, inténtelo de nuevo más tarde.";
        $_SESSION['message_type'] = "error";
        // Redirigir a la página de índice en caso de error grave
        header("Location: " . $root_path . "modules/centros_trabajo/index.php");
        exit();
    } finally {
        $conn->close();
    }
} else {
    $_SESSION['message'] = "Solicitud no válida.";
    $_SESSION['message_type'] = "error";
    header("Location: " . $root_path . "dashboard.php");
    exit();
}
?>