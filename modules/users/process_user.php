<?php
/**
 * modules/users/process_user.php
 *
 * Script para procesar acciones de usuarios como la eliminación.
 * Este script es invocado por redirecciones o llamadas AJAX.
 * Solo accesible por el perfil 'Administrador'.
 */

session_start();

// Incluye el archivo de verificación de sesión para asegurar que el usuario esté logueado.
require_once '../../includes/session_check.php';

// Verifica si el usuario logueado tiene el perfil 'Administrador'.
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Acceso denegado. No tiene permisos para realizar esta acción.'];
    header("location: ../../dashboard.php");
    exit;
}

require_once '../../includes/db_connection.php';

// Verifica la acción a realizar
if (isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'delete':
            if (isset($_GET['id']) && !empty(trim($_GET['id']))) {
                $id_usuario = filter_var(trim($_GET['id']), FILTER_VALIDATE_INT);

                if ($id_usuario === false) {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'ID de usuario no válido para eliminar.'];
                    header("location: index.php");
                    exit();
                }

                // Iniciar una transacción para asegurar que tanto la eliminación de perfiles como del usuario sean atómicas
                $conn->begin_transaction();
                try {
                    // 1. Eliminar los perfiles asociados al usuario en usuario_perfil
                    $sql_delete_user_profiles = "DELETE FROM usuario_perfil WHERE id_usuario = ?";
                    if ($stmt_profiles = $conn->prepare($sql_delete_user_profiles)) {
                        $stmt_profiles->bind_param("i", $id_usuario);
                        $stmt_profiles->execute();
                        $stmt_profiles->close();
                    } else {
                        throw new Exception("Error al preparar la eliminación de perfiles de usuario: " . $conn->error);
                    }

                    // 2. Eliminar el usuario de la tabla usuarios
                    $sql_delete_user = "DELETE FROM usuarios WHERE id_usuario = ?";
                    if ($stmt_user = $conn->prepare($sql_delete_user)) {
                        $stmt_user->bind_param("i", $id_usuario);
                        if ($stmt_user->execute()) {
                            if ($stmt_user->affected_rows > 0) {
                                $conn->commit(); // Confirmar la transacción
                                $_SESSION['message'] = ['type' => 'success', 'text' => 'Usuario eliminado exitosamente.'];
                            } else {
                                throw new Exception("No se encontró el usuario con ID " . $id_usuario . " para eliminar.");
                            }
                        } else {
                            throw new Exception("Error al eliminar el usuario: " . $stmt_user->error);
                        }
                        $stmt_user->close();
                    } else {
                        throw new Exception("Error al preparar la eliminación de usuario: " . $conn->error);
                    }

                } catch (Exception $e) {
                    $conn->rollback(); // Revertir la transacción en caso de error
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar el usuario: ' . $e->getMessage()];
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'ID de usuario no proporcionado para eliminar.'];
            }
            break;

        // Puedes añadir más casos aquí para otras acciones (ej. activar/desactivar, resetear contraseña, etc.)
        default:
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Acción no válida.'];
            break;
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'No se especificó ninguna acción.'];
}

$conn->close();
header("location: index.php"); // Redirige de vuelta a la lista de usuarios
exit;
?>