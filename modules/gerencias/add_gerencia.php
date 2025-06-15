<?php
/**
 * modules/gerencias/add_gerencia.php
 *
 * Página para añadir una nueva Gerencia al sistema.
 * Contiene un formulario para la entrada de datos y valida la unicidad del nombre y código.
 * Solo accesible por el perfil 'Administrador'.
 *
 * Adaptado a la estructura de tabla con 'codigo_gerencia' y 'activo', y sin 'descripcion'.
 */

session_start();

// Incluye el archivo de verificación de sesión y conexión a la base de datos.
require_once '../../includes/session_check.php';

// Verifica si el usuario logueado tiene el perfil 'Administrador'.
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Acceso denegado. No tiene permisos para añadir gerencias.'];
    header("location: ../../dashboard.php");
    exit;
}

require_once '../../includes/db_connection.php';

$codigo_gerencia = $nombre_gerencia = "";
$activo = 1; // Por defecto activa al añadir una nueva gerencia
$codigo_gerencia_err = $nombre_gerencia_err = "";

// Procesa el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar código de gerencia
    if (empty(trim($_POST["codigo_gerencia"]))) {
        $codigo_gerencia_err = "Por favor, ingrese el código de la gerencia.";
    } else {
        $codigo_gerencia_input = trim($_POST["codigo_gerencia"]);
        // Preparar una sentencia SELECT para verificar si el código ya existe
        $sql_check_codigo = "SELECT id_gerencia FROM gerencias WHERE codigo_gerencia = ?";
        if ($stmt = $conn->prepare($sql_check_codigo)) {
            $stmt->bind_param("s", $param_codigo_gerencia);
            $param_codigo_gerencia = $codigo_gerencia_input;

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $codigo_gerencia_err = "Este código de gerencia ya está registrado.";
                } else {
                    $codigo_gerencia = $codigo_gerencia_input;
                }
            } else {
                echo "¡Ups! Algo salió mal al verificar el código. Por favor, inténtelo de nuevo más tarde.";
            }
            $stmt->close();
        }
    }

    // Validar nombre de gerencia
    if (empty(trim($_POST["nombre_gerencia"]))) {
        $nombre_gerencia_err = "Por favor, ingrese el nombre de la gerencia.";
    } else {
        $nombre_gerencia_input = trim($_POST["nombre_gerencia"]);
        // Preparar una sentencia SELECT para verificar si el nombre ya existe
        $sql_check_nombre = "SELECT id_gerencia FROM gerencias WHERE nombre_gerencia = ?";
        if ($stmt = $conn->prepare($sql_check_nombre)) {
            $stmt->bind_param("s", $param_nombre_gerencia);
            $param_nombre_gerencia = $nombre_gerencia_input;

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $nombre_gerencia_err = "Esta gerencia ya está registrada con ese nombre.";
                } else {
                    $nombre_gerencia = $nombre_gerencia_input;
                }
            } else {
                echo "¡Ups! Algo salió mal al verificar el nombre. Por favor, inténtelo de nuevo más tarde.";
            }
            $stmt->close();
        }
    }

    // Obtener estado activo/inactivo del checkbox
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Si no hay errores de entrada, insertar la gerencia en la base de datos
    if (empty($codigo_gerencia_err) && empty($nombre_gerencia_err)) {
        // La tabla tiene 'codigo_gerencia', 'nombre_gerencia', 'activo'
        $sql_insert = "INSERT INTO gerencias (codigo_gerencia, nombre_gerencia, activo) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql_insert)) {
            $stmt->bind_param("ssi", $param_codigo_gerencia, $param_nombre_gerencia, $param_activo);
            $param_codigo_gerencia = $codigo_gerencia;
            $param_nombre_gerencia = $nombre_gerencia;
            $param_activo = $activo;

            if ($stmt->execute()) {
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Gerencia "' . htmlspecialchars($nombre_gerencia) . '" (Código: ' . htmlspecialchars($codigo_gerencia) . ') añadida exitosamente.'];
                header("location: index.php");
                exit();
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al añadir la gerencia: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al preparar la consulta: ' . $conn->error];
        }
    }
    $conn->close();
}
// Si es GET request, las variables se inicializan vacías y 'activo' en 1.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Gerencia - Ferrominera SST</title>
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
        <h1>Añadir Nueva Gerencia</h1>
        <p>Complete el siguiente formulario para añadir una nueva gerencia al sistema.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_METHOD"]); ?>" method="post" class="data-form">
            <div class="form-group">
                <label for="codigo_gerencia">Código de Gerencia:</label>
                <input type="text" id="codigo_gerencia" name="codigo_gerencia" class="form-control <?php echo (!empty($codigo_gerencia_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($codigo_gerencia); ?>" required maxlength="20">
                <span class="invalid-feedback"><?php echo $codigo_gerencia_err; ?></span>
            </div>
            <div class="form-group">
                <label for="nombre_gerencia">Nombre de la Gerencia:</label>
                <input type="text" id="nombre_gerencia" name="nombre_gerencia" class="form-control <?php echo (!empty($nombre_gerencia_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($nombre_gerencia); ?>" required maxlength="100">
                <span class="invalid-feedback"><?php echo $nombre_gerencia_err; ?></span>
            </div>
            <div class="form-group">
                <label class="checkbox-container">
                    <input type="checkbox" name="activo" value="1" <?php echo ($activo == 1) ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Activo
                </label>
            </div>

            <div class="form-actions">
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Guardar Gerencia</button>
            </div>
        </form>
    </div>

    <script src="../../js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica para los submenús (importante para que el menú funcione en esta página)
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

            // Función para convertir inputs de texto a mayúsculas
            document.querySelectorAll('input[type="text"]').forEach(function(input) {
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });
        });
    </script>
</body>
</html>