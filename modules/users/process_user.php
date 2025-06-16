<?php
// modules/users/process_user.php - Procesa acciones como eliminar usuarios

require_once __DIR__ . '/../../includes/session_check.php';
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = 'No tienes permiso para realizar esta acción.';
    header('Location: ' . '../../dashboard.php');
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';

$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? null;

if ($action == 'delete' && $user_id) {
    // Evitar que un administrador se elimine a sí mismo (opcional pero recomendado)
    if ($_SESSION['id_usuario'] == $user_id) {
        $_SESSION['message'] = 'No puedes eliminar tu propia cuenta de usuario.';
        header('Location: index.php');
        exit();
    }

    // Obtener el username antes de eliminar para el mensaje de éxito/error
    $stmt_get_username = $conn->prepare("SELECT username FROM usuarios WHERE id_usuario = ?");
    $stmt_get_username->bind_param("i", $user_id);
    $stmt_get_username->execute();
    $stmt_get_username->bind_result($username_to_delete);
    $stmt_get_username->fetch();
    $stmt_get_username->close();

    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Usuario "' . htmlspecialchars($username_to_delete) . '" eliminado exitosamente.';
    } else {
        // SQLSTATE[23000]: Integrity constraint violation: 1451
        if ($stmt->errno == 1451) {
            $_SESSION['message'] = 'No se puede eliminar el usuario "' . htmlspecialchars($username_to_delete) . '" porque está asociado a otros registros en el sistema.';
        } else {
            $_SESSION['message'] = 'Error al eliminar el usuario "' . htmlspecialchars($username_to_delete) . '": ' . $stmt->error;
        }
    }
    $stmt->close();
} else {
    $_SESSION['message'] = 'Acción inválida o ID de usuario no especificado.';
}

$conn->close();
header('Location: index.php');
exit();
?>