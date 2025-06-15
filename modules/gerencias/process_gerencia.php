<?php
/**
 * modules/gerencias/process_gerencia.php
 *
 * Script para procesar acciones relacionadas con Gerencias, como la eliminación.
 * Este script es invocado por redirecciones o llamadas AJAX.
 * Solo accesible por el perfil 'Administrador'.
 *
 * Adaptado a la estructura de tabla con 'codigo_gerencia' y 'activo', y sin 'descripcion'.
 */

session_start();

// Incluye el archivo de verificación de sesión y conexión a la base de datos.
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
                $id_gerencia = filter_var(trim($_GET['id']), FILTER_VALIDATE_INT);

                if ($id_gerencia === false) {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'ID de gerencia no válido para eliminar.'];
                    header("location: index.php");
                    exit();
                }

                // **CONSIDERACIÓN IMPORTANTE:**
                // Antes de eliminar una gerencia, se debería verificar si está siendo
                // referenciada por otras tablas (ej. centros de trabajo, usuarios, etc.).
                // Por ahora, asumimos que no hay dependencias directas en cascada,
                // pero en un sistema real, se podría:
                // 1. Impedir la eliminación si hay registros dependientes.
                // 2. Desasignar o mover los registros dependientes a otra gerencia (requiere lógica adicional).
                // 3. Configurar la clave foránea en la base de datos con ON DELETE CASCADE (riesgoso).

                // Prepara la sentencia DELETE
                $sql_delete = "DELETE FROM gerencias WHERE id_gerencia = ?";
                if ($stmt = $conn->prepare($sql_delete)) {
                    $stmt->bind_param("i", $id_gerencia);
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            $_SESSION['message'] = ['type' => 'success', 'text' => 'Gerencia eliminada exitosamente.'];
                        } else {
                            $_SESSION['message'] = ['type' => 'warning', 'text' => 'No se encontró la gerencia con ID ' . $id_gerencia . '.'];
                        }
                    } else {
                        // Capturar errores de integridad referencial si existen
                        if ($conn->errno == 1451) { // Código de error para Foreign Key Constraint Fails
                            $_SESSION['message'] = ['type' => 'error', 'text' => 'No se puede eliminar la gerencia porque está asociada a otros registros (ej. Centros de Trabajo). Elimine primero los registros asociados.'];
                        } else {
                            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al eliminar la gerencia: ' . $stmt->error];
                        }
                    }
                    $stmt->close();
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al preparar la consulta de eliminación: ' . $conn->error];
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'ID de gerencia no proporcionado para eliminar.'];
            }
            break;

        default:
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Acción no válida.'];
            break;
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'No se especificó ninguna acción.'];
}

$conn->close();
header("location: index.php"); // Redirige de vuelta a la lista de gerencias
exit;
?>