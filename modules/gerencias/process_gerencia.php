<?php
// modules/gerencias/process_gerencia.php - Procesa acciones como eliminar gerencias

require_once __DIR__ . '/../../includes/session_check.php';
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = 'No tienes permiso para realizar esta acción.';
    header('Location: ' . '../../dashboard.php');
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';

$action = $_GET['action'] ?? '';
$gerencia_id = $_GET['id'] ?? null;

if ($action == 'delete' && $gerencia_id) {
    $stmt = $conn->prepare("DELETE FROM gerencias WHERE id_gerencia = ?");
    $stmt->bind_param("i", $gerencia_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Gerencia eliminada exitosamente.';
    } else {
        // SQLSTATE[23000]: Integrity constraint violation: 1451
        if ($stmt->errno == 1451) {
            $_SESSION['message'] = 'No se puede eliminar la gerencia porque está asociada a otros registros (ej. usuarios, centros de trabajo).';
        } else {
            $_SESSION['message'] = 'Error al eliminar la gerencia: ' . $stmt->error;
        }
    }
    $stmt->close();
} else {
    $_SESSION['message'] = 'Acción inválida o ID de gerencia no especificado.';
}

$conn->close();
header('Location: index.php');
exit();
?>