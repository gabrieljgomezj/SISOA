<?php
// modules/users/index.php - Listado y gestión de usuarios

require_once __DIR__ . '/../../includes/session_check.php';
// Asegurarse de que solo los administradores puedan acceder a este módulo
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = 'No tienes permiso para acceder a esta sección.';
    header('Location: ' . '../../dashboard.php'); // Redirigir a dashboard o a una página de error
    exit();
}

require_once __DIR__ . '/../../includes/db_connection.php';

// Variables para búsqueda y paginación
$search_query = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Número de usuarios por página
$offset = ($page - 1) * $limit;

// Seleccionar la columna 'rol'
$sql_count = "SELECT COUNT(*) FROM usuarios";
$sql_select = "SELECT id_usuario, username, nombre, apellido, cedula, ficha, correo, activo, rol FROM usuarios"; // **INCLUIDA LA COLUMNA 'rol'**
$where_clauses = [];
$params = [];
$types = '';

if (!empty($search_query)) {
    $where_clauses[] = "(username LIKE ? OR nombre LIKE ? OR apellido LIKE ? OR cedula LIKE ? OR correo LIKE ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    $types .= 'sssss';
}

if (!empty($where_clauses)) {
    $sql_count .= " WHERE " . implode(" AND ", $where_clauses);
    $sql_select .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql_select .= " ORDER BY username ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Contar total de registros
$stmt_count = $conn->prepare($sql_count);
if ($stmt_count === false) {
    die('Error al preparar la consulta de conteo: ' . $conn->error);
}
if (!empty($types) && count($params) > 2) { // Excluir los últimos 2 de limit/offset que son para paginación
    $bind_params_count = array_slice($params, 0, count($params) - 2);
    $bind_types_count = substr($types, 0, strlen($types) - 2);
    $stmt_count->bind_param($bind_types_count, ...$bind_params_count);
}
$stmt_count->execute();
$stmt_count->bind_result($total_users);
$stmt_count->fetch();
$stmt_count->close();

$total_pages = ceil($total_users / $limit);

// Obtener usuarios para la página actual
$stmt_select = $conn->prepare($sql_select);
if ($stmt_select === false) {
    die('Error al preparar la consulta de selección: ' . $conn->error);
}
$stmt_select->bind_param($types, ...$params);
$stmt_select->execute();
$result = $stmt_select->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt_select->close();
$conn->close();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Ferrominera SST</title>
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/main_header.php'; ?>

    <main class="content">
        <div class="container">
            <h1>Gestión de Usuarios</h1>

            <?php if ($message): ?>
                <p class="message <?php echo htmlspecialchars($message); ?>"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>

            <div class="actions-bar">
                <form action="index.php" method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Buscar usuario por nombre, cédula, etc." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-search" title="Buscar">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    </button>
                </form>
                <a href="add_user.php" class="btn btn-success" title="Añadir Nuevo Usuario">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 4c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm5 9h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                    <span>Añadir Usuario</span>
                </a>
            </div>

            <?php if (empty($users)): ?>
                <p>No se encontraron usuarios.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Correo</th>
                                <th>Rol</th> <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id_usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></td>
                                    <td><?php echo htmlspecialchars($user['cedula']); ?></td>
                                    <td><?php echo htmlspecialchars($user['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($user['rol']); ?></td> <td><?php echo $user['activo'] ? 'Activo' : 'Inactivo'; ?></td>
                                    <td class="actions">
                                        <a href="edit_user.php?id=<?php echo $user['id_usuario']; ?>" class="btn btn-info btn-icon" title="Editar Usuario">
                                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                        </a>
                                        <a href="process_user.php?action=delete&id=<?php echo $user['id_usuario']; ?>" class="btn btn-danger btn-icon delete-confirm" data-name="<?php echo htmlspecialchars($user['username']); ?>" title="Eliminar Usuario">
                                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5L13.5 2H10L9.5 4H5v2h14V4z"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>" class="btn btn-secondary">Anterior</a>
                    <?php endif; ?>
                    <span>Página <?php echo $page; ?> de <?php echo $total_pages; ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>" class="btn btn-secondary">Siguiente</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php // require_once __DIR__ . '/../../includes/footer.php'; ?>
    <script src="../../js/main.js"></script>
    <script>
        document.querySelectorAll('.delete-confirm').forEach(button => {
            button.addEventListener('click', function(e) {
                const userName = this.getAttribute('data-name');
                if (!confirm(`¿Estás seguro de que deseas eliminar al usuario "${userName}"? Esta acción no se puede deshacer.`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>