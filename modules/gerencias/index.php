<?php
/**
 * modules/gerencias/index.php
 *
 * Página principal para la gestión de Gerencias.
 * Muestra un listado de gerencias, permite la búsqueda y ofrece opciones para añadir,
 * editar y eliminar gerencias.
 * Solo accesible por el perfil 'Administrador'.
 *
 * Adaptado a la estructura de tabla con 'codigo_gerencia' y 'activo', y sin 'descripcion'.
 */

session_start();

// Incluye el archivo de verificación de sesión y conexión a la base de datos.
require_once '../../includes/session_check.php';
require_once '../../includes/db_connection.php';

// Verifica si el usuario logueado tiene el perfil 'Administrador'.
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Acceso denegado. No tiene permisos para gestionar gerencias.'];
    header("location: ../../dashboard.php");
    exit;
}

// Inicializa variables para la paginación
$limit = 10; // Número de registros por página
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Inicializa la variable de búsqueda
$search_query = '';
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = trim($_GET['search']);
}

// Construye la consulta SQL base
// Seleccionamos 'activo' para mostrar su estado
$sql = "SELECT id_gerencia, codigo_gerencia, nombre_gerencia, activo FROM gerencias";
$count_sql = "SELECT COUNT(id_gerencia) AS total FROM gerencias";
$params = [];
$types = "";

// Añade la condición de búsqueda si existe
if (!empty($search_query)) {
    // La búsqueda ahora incluirá 'codigo_gerencia' y 'nombre_gerencia'
    $sql .= " WHERE codigo_gerencia LIKE ? OR nombre_gerencia LIKE ?";
    $count_sql .= " WHERE codigo_gerencia LIKE ? OR nombre_gerencia LIKE ?";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

$sql .= " ORDER BY nombre_gerencia ASC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$gerencias = [];
$total_gerencias = 0;

// Obtener el total de registros para la paginación
if ($stmt_count = $conn->prepare($count_sql)) {
    if (!empty($types) && count($params) > 2) { // Si hay búsqueda, bind los parámetros de búsqueda (excluyendo limit y offset)
        $search_params_count = array_slice($params, 0, count($params) - 2);
        $search_types_count = substr($types, 0, strlen($types) - 2);
        call_user_func_array([$stmt_count, 'bind_param'], array_merge([$search_types_count], $search_params_count));
    }
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_gerencias = $result_count->fetch_assoc()['total'];
    $stmt_count->close();
}

// Obtener los datos de las gerencias
if ($stmt = $conn->prepare($sql)) {
    call_user_func_array([$stmt, 'bind_param'], array_merge([$types], $params));
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $gerencias[] = $row;
        }
    }
    $stmt->close();
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al preparar la consulta de gerencias: ' . $conn->error];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Gerencias - Ferrominera SST</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="icon" href="../../img/favicon.ico" type="image/x-icon">
</head>
<body>
    <header class="main-header">
        <nav class="main-nav">
            <div class="nav-brand">
                <img src="../../img/logo.svg" alt="Logo Ferrominera">
            </div>
            <ul class="nav-menu">
                <?php if (in_array('Administrador', $_SESSION['perfiles'])): ?>
                    <li class="nav-item has-submenu">
                        <a href="#" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.38 0 2.5 1.12 2.5 2.5S13.38 10 12 10 9.5 8.88 9.5 7.5 10.62 5 12 5zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.88 6-3.88s5.97 1.89 6 3.88c-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
                            <span>Configuración</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="../users/index.php">Gestión de Usuarios</a></li>
                            </ul>
                    </li>
                    <li class="nav-item has-submenu">
                        <a href="#" class="nav-link active">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                            <span>Mantenimiento</span>
                        </a>
                        <ul class="submenu active">
                            <li><a href="index.php" class="active">Gerencias</a></li>
                            <li><a href="../centros_trabajo/index.php">Centros de Trabajo</a></li>
                            <li><a href="../tipos_condiciones/index.php">Tipos Condiciones Inseguras</a></li>
                            </ul>
                    </li>
                <?php endif; ?>

                <?php if (in_array('Administrador', $_SESSION['perfiles']) || in_array('Operador', $_SESSION['perfiles'])): ?>
                    <li class="nav-item">
                        <a href="../../dashboard.php" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                            <span>Condiciones Inseguras</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.7-7 8.94V12H5V6.3l7-3.5 7 3.5v5.69z"/></svg>
                            <span>Accidentes de Trabajo</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            <span>Formación</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-8zm-2 16c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/></svg>
                            <span>Planes de Trabajo</span>
                        </a>
                    </li>
                    <li class="nav-item has-submenu">
                        <a href="#" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"/></svg>
                            <span>Reportes</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="#">Condiciones Inseguras</a></li>
                            <li><a href="#">Accidentes de Trabajo</a></li>
                            </ul>
                    </li>
                <?php endif; ?>

                <?php if (in_array('Administrador', $_SESSION['perfiles']) || in_array('Consultor', $_SESSION['perfiles'])): ?>
                    <?php if (!in_array('Administrador', $_SESSION['perfiles']) && !in_array('Operador', $_SESSION['perfiles'])): ?>
                        <li class="nav-item">
                            <a href="../../dashboard.php" class="nav-link">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item has-submenu">
                            <a href="#" class="nav-link">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"/></svg>
                                <span>Reportes</span>
                            </a>
                            <ul class="submenu">
                                <li><a href="#">Condiciones Inseguras</a></li>
                                <li><a href="#">Accidentes de Trabajo</a></li>
                                </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <li class="nav-item">
                    <a href="../../logout.php" class="nav-link logout-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="main-content">
        <h1>Gestión de Gerencias</h1>
        <p>Administre las gerencias registradas en el sistema.</p>

        <?php
        // Mostrar mensajes de sesión si existen
        if (isset($_SESSION['message'])) {
            $message_type = $_SESSION['message']['type'];
            $message_text = $_SESSION['message']['text'];
            echo "<div class='alert alert-$message_type'>$message_text</div>";
            unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
        }
        ?>

        <div class="actions-bar">
            <a href="add_gerencia.php" class="btn btn-success">
                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                <span>Añadir Gerencia</span>
            </a>
            <form action="index.php" method="get" class="search-form">
                <input type="text" name="search" placeholder="Buscar gerencia..." value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit" class="btn btn-search">
                    <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                </button>
            </form>
        </div>

        <?php if (!empty($gerencias)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th> <th>Nombre de Gerencia</th>
                        <th>Estado</th> <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gerencias as $gerencia): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($gerencia['id_gerencia']); ?></td>
                        <td><?php echo htmlspecialchars($gerencia['codigo_gerencia']); ?></td>
                        <td><?php echo htmlspecialchars($gerencia['nombre_gerencia']); ?></td>
                        <td>
                            <span class="status-badge <?php echo ($gerencia['activo'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo ($gerencia['activo'] == 1) ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_gerencia.php?id=<?php echo htmlspecialchars($gerencia['id_gerencia']); ?>" class="btn-icon" title="Editar">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                            </a>
                            <a href="#" class="btn-icon delete-btn" data-id="<?php echo htmlspecialchars($gerencia['id_gerencia']); ?>" data-name="<?php echo htmlspecialchars($gerencia['nombre_gerencia']); ?>" title="Eliminar">
                                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5L13.5 3h-5L7.5 4H4v2h16V4z"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="btn btn-pagination">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="btn btn-pagination <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?><?php echo !empty($search_query) ? '&search=' . urlencode($search_query) : ''; ?>" class="btn btn-pagination">Siguiente</a>
            <?php endif; ?>
        </div>

        <?php else: ?>
            <p class="no-results">No se encontraron gerencias. <a href="add_gerencia.php">Añadir la primera gerencia.</a></p>
        <?php endif; ?>
    </div>

    <div id="deleteConfirmationModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Confirmar Eliminación</h2>
            <p>¿Está seguro de que desea eliminar la gerencia "<strong id="gerenciaName"></strong>"? Esta acción no se puede deshacer.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" id="cancelDelete">Cancelar</button>
                <button class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>

    <script src="../../js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica de submenú (para que el menú funcione en esta página)
            document.querySelectorAll('.has-submenu > .nav-link').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const submenu = this.nextElementSibling;
                    if (submenu && submenu.classList.contains('submenu')) {
                        document.querySelectorAll('.submenu.active').forEach(openSubmenu => {
                            if (openSubmenu !== submenu) {
                                openSubmenu.classList.remove('active');
                            }
                        });
                        submenu.classList.toggle('active');
                    }
                });
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.has-submenu')) {
                    document.querySelectorAll('.submenu.active').forEach(openSubmenu => {
                        openSubmenu.classList.remove('active');
                    });
                }
            });

            // Lógica del modal de eliminación (similar a users/index.php)
            const deleteModal = document.getElementById('deleteConfirmationModal');
            const closeButton = document.querySelector('.modal .close-button');
            const cancelDeleteButton = document.getElementById('cancelDelete');
            const confirmDeleteButton = document.getElementById('confirmDelete');
            const gerenciaNameSpan = document.getElementById('gerenciaName');
            let gerenciaIdToDelete = null;

            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    gerenciaIdToDelete = this.dataset.id;
                    const gerenciaName = this.dataset.name;
                    gerenciaNameSpan.textContent = gerenciaName;
                    deleteModal.style.display = 'block';
                });
            });

            closeButton.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                gerenciaIdToDelete = null;
            });

            cancelDeleteButton.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                gerenciaIdToDelete = null;
            });

            confirmDeleteButton.addEventListener('click', function() {
                if (gerenciaIdToDelete) {
                    window.location.href = 'process_gerencia.php?action=delete&id=' + gerenciaIdToDelete;
                }
            });

            window.addEventListener('click', function(event) {
                if (event.target === deleteModal) {
                    deleteModal.style.display = 'none';
                    gerenciaIdToDelete = null;
                }
            });
        });
    </script>
</body>
</html>