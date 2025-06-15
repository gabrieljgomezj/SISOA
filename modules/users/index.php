<?php
/**
 * modules/users/index.php
 *
 * Página principal del módulo de gestión de usuarios.
 * Muestra un listado de todos los usuarios registrados, permitiendo buscar,
 * añadir, editar y eliminar usuarios. Solo accesible por el perfil 'Administrador'.
 */

session_start();

// Incluye el archivo de verificación de sesión para asegurar que el usuario esté logueado.
require_once '../../includes/session_check.php';

// Verifica si el usuario logueado tiene el perfil 'Administrador'.
// Si no es Administrador, lo redirige al dashboard con un mensaje de acceso denegado.
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Acceso denegado. No tiene permisos para acceder a esta sección.'];
    header("location: ../../dashboard.php");
    exit;
}

require_once '../../includes/db_connection.php';

// Inicializa las variables para la búsqueda
$search_query = "";
$where_clause = "";
$params = [];
$types = "";

// Procesa la búsqueda si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    if (!empty($search_query)) {
        // Construye la cláusula WHERE para buscar en múltiples campos
        $where_clause = " WHERE u.nombre LIKE ? OR u.apellido LIKE ? OR u.cedula LIKE ? OR u.username LIKE ? OR u.correo LIKE ?";
        $param_like = '%' . $search_query . '%';
        $params = [$param_like, $param_like, $param_like, $param_like, $param_like];
        $types = "sssss";
    }
}

// Prepara la consulta SQL para obtener todos los usuarios y sus perfiles
// Usamos LEFT JOIN para asegurarnos de que incluso los usuarios sin perfil asignado se muestren (aunque no debería ocurrir con nuestro diseño)
$sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.cedula, u.ficha, u.correo, u.username, u.activo,
               GROUP_CONCAT(p.nombre_perfil ORDER BY p.nombre_perfil ASC SEPARATOR ', ') AS perfiles
        FROM usuarios u
        LEFT JOIN usuario_perfil up ON u.id_usuario = up.id_usuario
        LEFT JOIN perfiles p ON up.id_perfil = p.id_perfil" .
        $where_clause .
        " GROUP BY u.id_usuario ORDER BY u.apellido ASC, u.nombre ASC";

$stmt = null; // Inicializa $stmt a null

if (!empty($where_clause)) {
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error al preparar la consulta con búsqueda: " . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
    if ($result === false) {
        die("Error al ejecutar la consulta: " . $conn->error);
    }
}

// Manejo de mensajes de notificación
$notification_type = '';
$notification_message = '';
if (isset($_SESSION['message'])) {
    $notification_type = $_SESSION['message']['type'];
    $notification_message = $_SESSION['message']['text'];
    unset($_SESSION['message']); // Limpiar el mensaje después de mostrarlo
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Ferrominera SST</title>
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
                            <li><a href="../users/index.php" class="active">Gestión de Usuarios</a></li>
                            </ul>
                    </li>
                    <li class="nav-item has-submenu">
                        <a href="#" class="nav-link">
                            <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
                            <span>Mantenimiento</span>
                        </a>
                        <ul class="submenu">
                            <li><a href="#">Gerencias</a></li>
                            <li><a href="#">Centros de Trabajo</a></li>
                            <li><a href="#">Tipos Condiciones Inseguras</a></li>
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
        <h1>Gestión de Usuarios</h1>
        <p>Aquí puede administrar todos los usuarios del sistema Ferrominera SST.</p>

        <?php if (!empty($notification_message)): ?>
            <div class="notification-message <?php echo $notification_type; ?>" id="dynamicNotification">
                <?php echo htmlspecialchars($notification_message); ?>
            </div>
        <?php endif; ?>

        <div class="action-bar">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Buscar por nombre, cédula, usuario..." value="<?php echo htmlspecialchars($search_query); ?>" class="form-control search-input">
                <button type="submit" class="btn btn-secondary">Buscar</button>
            </form>
            <a href="add_user.php" class="btn btn-primary btn-add-new">
                <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                <span>Nuevo Usuario</span>
            </a>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Cédula</th>
                            <th>Ficha</th>
                            <th>Usuario</th>
                            <th>Correo</th>
                            <th>Perfiles</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                <td><?php echo htmlspecialchars($row['ficha'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['correo']); ?></td>
                                <td><?php echo htmlspecialchars($row['perfiles'] ?? 'Sin Perfil'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($row['activo'] == 1) ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ($row['activo'] == 1) ? 'ACTIVO' : 'INACTIVO'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="edit_user.php?id=<?php echo $row['id_usuario']; ?>" title="Editar Usuario" class="action-btn edit-btn">
                                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id_usuario']; ?>, '<?php echo htmlspecialchars($row['nombre'] . ' ' . $row['apellido']); ?>');" title="Eliminar Usuario" class="action-btn delete-btn">
                                        <svg class="icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5L13 2H9L7.5 4H5v2h14V4z"/></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No se encontraron usuarios registrados.</p>
        <?php endif; ?>
    </div>

    <div id="deleteConfirmationModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Confirmar Eliminación</h2>
            <p>¿Está seguro de que desea eliminar al usuario <strong id="deleteUserName"></strong>?</p>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-primary btn-delete-confirm" id="confirmDeleteButton">Eliminar</button>
            </div>
        </div>
    </div>

    <script src="../../js/main.js"></script>
    <script>
        // Lógica JS para el menú desplegable (ya debería estar en main.js o replicado)
        document.addEventListener('DOMContentLoaded', function() {
            // Asegúrate de que la función de notificaciones se dispare al cargar la página si hay un mensaje
            const dynamicNotification = document.getElementById('dynamicNotification');
            if (dynamicNotification) {
                showNotification(dynamicNotification.classList[1], dynamicNotification.textContent, 6000);
                dynamicNotification.remove(); // Elimina el div para evitar duplicidad o problemas
            }

            // Lógica para los submenús (copiada del dashboard, idealmente en un archivo JS compartido)
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

            // Lógica para el modal de confirmación de eliminación
            const modal = document.getElementById('deleteConfirmationModal');
            const closeButton = modal.querySelector('.close-button');
            const confirmButton = document.getElementById('confirmDeleteButton');
            let userIdToDelete = null;

            window.confirmDelete = function(id, name) {
                userIdToDelete = id;
                document.getElementById('deleteUserName').textContent = name;
                modal.classList.add('active'); // Usa una clase 'active' para mostrar el modal
            };

            window.closeModal = function() {
                modal.classList.remove('active');
                userIdToDelete = null;
            };

            closeButton.addEventListener('click', closeModal);
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    closeModal();
                }
            });

            confirmButton.addEventListener('click', function() {
                if (userIdToDelete !== null) {
                    window.location.href = 'process_user.php?action=delete&id=' + userIdToDelete;
                }
            });
        });
    </script>
</body>
</html>