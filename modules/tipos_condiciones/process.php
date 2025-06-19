<?php
// modules/tipos_condiciones/process.php

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
                $nombre_tipo = trim($_POST['nombre_tipo'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $activo = isset($_POST['activo']) ? 1 : 0;

                if (empty($nombre_tipo)) {
                    $_SESSION['message'] = "El nombre del tipo de condición es obligatorio.";
                    $_SESSION['message_type'] = "error";
                    $_SESSION['old_data'] = $_POST;
                    header("Location: " . $root_path . "modules/tipos_condiciones/create.php");
                    exit();
                }

                // Verificar si el nombre ya existe
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM tipos_condiciones WHERE nombre_tipo = ?");
                $stmt_check->bind_param("s", $nombre_tipo);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    $_SESSION['message'] = "Ya existe un tipo de condición con este nombre. Por favor, verifique.";
                    $_SESSION['message_type'] = "error";
                    $_SESSION['old_data'] = $_POST;
                    header("Location: " . $root_path . "modules/tipos_condiciones/create.php");
                    exit();
                }

                $stmt = $conn->prepare("INSERT INTO tipos_condiciones (nombre_tipo, descripcion, activo) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $nombre_tipo, $descripcion, $activo);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Tipo de condición creado exitosamente.";
                    $_SESSION['message_type'] = "success";
                } else {
                    throw new Exception("Error al crear el tipo de condición: " . $stmt->error);
                }
                $stmt->close();
                header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
                exit();

            case 'update':
                $id_tipo_condicion = intval($_POST['id_tipo_condicion'] ?? 0);
                $nombre_tipo = trim($_POST['nombre_tipo'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $activo = isset($_POST['activo']) ? 1 : 0;

                if ($id_tipo_condicion === 0 || empty($nombre_tipo)) {
                    $_SESSION['message'] = "Datos incompletos para actualizar el tipo de condición.";
                    $_SESSION['message_type'] = "error";
                    $redirect_url = ($id_tipo_condicion !== 0) ? $root_path . "modules/tipos_condiciones/edit.php?id=" . $id_tipo_condicion : $root_path . "modules/tipos_condiciones/index.php";
                    header("Location: " . $redirect_url);
                    exit();
                }

                // Verificar si el nombre ya existe para OTRO tipo (excluyendo el actual)
                $stmt_check = $conn->prepare("SELECT COUNT(*) FROM tipos_condiciones WHERE nombre_tipo = ? AND id_tipo_condicion != ?");
                $stmt_check->bind_param("si", $nombre_tipo, $id_tipo_condicion);
                $stmt_check->execute();
                $stmt_check->bind_result($count);
                $stmt_check->fetch();
                $stmt_check->close();

                if ($count > 0) {
                    $_SESSION['message'] = "Ya existe otro tipo de condición con este nombre. Por favor, verifique.";
                    $_SESSION['message_type'] = "error";
                    header("Location: " . $root_path . "modules/tipos_condiciones/edit.php?id=" . $id_tipo_condicion);
                    exit();
                }

                $stmt = $conn->prepare("UPDATE tipos_condiciones SET nombre_tipo = ?, descripcion = ?, activo = ? WHERE id_tipo_condicion = ?");
                $stmt->bind_param("ssii", $nombre_tipo, $descripcion, $activo, $id_tipo_condicion);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Tipo de condición actualizado exitosamente.";
                    $_SESSION['message_type'] = "success";
                } else {
                    throw new Exception("Error al actualizar el tipo de condición: " . $stmt->error);
                }
                $stmt->close();
                header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
                exit();

            case 'delete':
                $id_tipo_condicion = intval($_POST['id_tipo_condicion'] ?? 0);

                if ($id_tipo_condicion === 0) {
                    $_SESSION['message'] = "ID de tipo de condición no especificado para eliminar.";
                    $_SESSION['message_type'] = "error";
                    header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
                    exit();
                }

                $stmt = $conn->prepare("DELETE FROM tipos_condiciones WHERE id_tipo_condicion = ?");
                $stmt->bind_param("i", $id_tipo_condicion);

                if ($stmt->execute()) {
                    $_SESSION['message'] = "Tipo de condición eliminado exitosamente.";
                    $_SESSION['message_type'] = "success";
                } else {
                    if ($conn->errno == 1451) { // Error de clave foránea
                        $_SESSION['message'] = "No se puede eliminar el tipo de condición porque está siendo utilizado en otros registros (ej. condiciones inseguras).";
                        $_SESSION['message_type'] = "error";
                    } else {
                        throw new Exception("Error al eliminar el tipo de condición: " . $stmt->error);
                    }
                }
                $stmt->close();
                header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
                exit();

            default:
                $_SESSION['message'] = "Acción no válida.";
                $_SESSION['message_type'] = "error";
                header("Location: " . $root_path . "dashboard.php");
                exit();
        }
    } catch (Exception $e) {
        error_log("Error en process.php (Tipos de Condiciones): " . $e->getMessage());
        $_SESSION['message'] = "Ocurrió un error inesperado. Por favor, inténtelo de nuevo más tarde.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $root_path . "modules/tipos_condiciones/index.php");
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