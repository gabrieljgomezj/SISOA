<?php
/**
 * modules/users/edit_user.php
 *
 * Página para editar un usuario existente en el sistema.
 * Carga los datos del usuario por su ID y permite modificarlos,
 * incluyendo sus perfiles y estado de actividad.
 * Solo accesible por el perfil 'Administrador'.
 */

session_start();

// Incluye el archivo de verificación de sesión para asegurar que el usuario esté logueado.
require_once '../../includes/session_check.php';

// Verifica si el usuario logueado tiene el perfil 'Administrador'.
if (!in_array('Administrador', $_SESSION['perfiles'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Acceso denegado. No tiene permisos para editar usuarios.'];
    header("location: ../../dashboard.php");
    exit;
}

require_once '../../includes/db_connection.php';

$id_usuario = $nombre = $apellido = $cedula = $ficha = $correo = $username = "";
$activo = 0; // Valor predeterminado para el checkbox
$current_perfiles_ids = []; // Array para almacenar los IDs de los perfiles asignados al usuario

// Mensajes de error para los campos
$nombre_err = $apellido_err = $cedula_err = $ficha_err = $correo_err = $username_err = $password_err = $confirm_password_err = $perfiles_err = "";

// Obtener todos los perfiles disponibles para mostrar en el formulario
$perfiles_disponibles = [];
$sql_perfiles = "SELECT id_perfil, nombre_perfil FROM perfiles ORDER BY nombre_perfil ASC";
$result_perfiles = $conn->query($sql_perfiles);
if ($result_perfiles->num_rows > 0) {
    while ($row = $result_perfiles->fetch_assoc()) {
        $perfiles_disponibles[] = $row;
    }
}

// Procesa el formulario cuando se envía (es decir, cuando se guarda la edición)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanear la entrada del ID de usuario
    $id_usuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    if (!$id_usuario) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'ID de usuario inválido.'];
        header("location: index.php");
        exit;
    }

    // Validar nombre
    if (empty(trim($_POST["nombre"]))) {
        $nombre_err = "Por favor, ingrese el nombre.";
    } else {
        $nombre = trim($_POST["nombre"]);
    }

    // Validar apellido
    if (empty(trim($_POST["apellido"]))) {
        $apellido_err = "Por favor, ingrese el apellido.";
    } else {
        $apellido = trim($_POST["apellido"]);
    }

    // Validar cédula (debe ser única, excluyendo al usuario actual)
    if (empty(trim($_POST["cedula"]))) {
        $cedula_err = "Por favor, ingrese el número de cédula.";
    } else {
        $cedula = trim($_POST["cedula"]);
        $sql_check_cedula = "SELECT id_usuario FROM usuarios WHERE cedula = ? AND id_usuario != ?";
        if ($stmt_check_cedula = $conn->prepare($sql_check_cedula)) {
            $stmt_check_cedula->bind_param("si", $param_cedula, $id_usuario);
            $param_cedula = $cedula;
            if ($stmt_check_cedula->execute()) {
                $stmt_check_cedula->store_result();
                if ($stmt_check_cedula->num_rows > 0) {
                    $cedula_err = "Esta cédula ya está registrada por otro usuario.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            $stmt_check_cedula->close();
        }
    }

    // Validar ficha (puede ser nula, pero si se ingresa, debe ser única, excluyendo al usuario actual)
    if (!empty(trim($_POST["ficha"]))) {
        $ficha = trim($_POST["ficha"]);
        $sql_check_ficha = "SELECT id_usuario FROM usuarios WHERE ficha = ? AND id_usuario != ?";
        if ($stmt_check_ficha = $conn->prepare($sql_check_ficha)) {
            $stmt_check_ficha->bind_param("si", $param_ficha, $id_usuario);
            $param_ficha = $ficha;
            if ($stmt_check_ficha->execute()) {
                $stmt_check_ficha->store_result();
                if ($stmt_check_ficha->num_rows > 0) {
                    $ficha_err = "Esta ficha ya está registrada por otro usuario.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            $stmt_check_ficha->close();
        }
    } else {
        $ficha = NULL;
    }

    // Validar correo (debe ser único y formato de correo, excluyendo al usuario actual)
    if (empty(trim($_POST["correo"]))) {
        $correo_err = "Por favor, ingrese el correo electrónico.";
    } elseif (!filter_var(trim($_POST["correo"]), FILTER_VALIDATE_EMAIL)) {
        $correo_err = "Formato de correo electrónico inválido.";
    } else {
        $correo = trim($_POST["correo"]);
        $sql_check_correo = "SELECT id_usuario FROM usuarios WHERE correo = ? AND id_usuario != ?";
        if ($stmt_check_correo = $conn->prepare($sql_check_correo)) {
            $stmt_check_correo->bind_param("si", $param_correo, $id_usuario);
            $param_correo = $correo;
            if ($stmt_check_correo->execute()) {
                $stmt_check_correo->store_result();
                if ($stmt_check_correo->num_rows > 0) {
                    $correo_err = "Este correo electrónico ya está registrado por otro usuario.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            $stmt_check_correo->close();
        }
    }

    // Validar nombre de usuario (debe ser único, excluyendo al usuario actual)
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor, ingrese un nombre de usuario.";
    } else {
        $username = trim($_POST["username"]);
        $sql_check_username = "SELECT id_usuario FROM usuarios WHERE username = ? AND id_usuario != ?";
        if ($stmt_check_username = $conn->prepare($sql_check_username)) {
            $stmt_check_username->bind_param("si", $param_username, $id_usuario);
            $param_username = $username;
            if ($stmt_check_username->execute()) {
                $stmt_check_username->store_result();
                if ($stmt_check_username->num_rows > 0) {
                    $username_err = "Este nombre de usuario ya está en uso por otro usuario.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            $stmt_check_username->close();
        }
    }

    // Validar contraseña solo si se ha ingresado una nueva
    $password_set = !empty(trim($_POST["password"]));
    $password_val = trim($_POST["password"]);
    $confirm_password_val = trim($_POST["confirm_password"]);

    if ($password_set) {
        if (strlen($password_val) < 6) {
            $password_err = "La contraseña debe tener al menos 6 caracteres.";
        }
        if (empty($confirm_password_val)) {
            $confirm_password_err = "Por favor, confirme la nueva contraseña.";
        } elseif ($password_val != $confirm_password_val) {
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }

    // Validar perfiles seleccionados
    if (isset($_POST['perfiles']) && !empty($_POST['perfiles'])) {
        $selected_perfiles = $_POST['perfiles'];
    } else {
        $selected_perfiles = []; // Asegurarse de que es un array vacío si no hay perfiles seleccionados
        $perfiles_err = "Debe seleccionar al menos un perfil para el usuario.";
    }
    // Para que los checkboxes mantengan su estado si hay un error en el POST
    $current_perfiles_ids = $selected_perfiles;


    // Validar estado activo/inactivo
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Si no hay errores de entrada, procede a actualizar el usuario
    if (empty($nombre_err) && empty($apellido_err) && empty($cedula_err) && empty($ficha_err) &&
        empty($correo_err) && empty($username_err) && empty($password_err) && empty($confirm_password_err) &&
        empty($perfiles_err)) {

        // Construir la consulta UPDATE dinámicamente si se va a cambiar la contraseña
        $sql_update_user = "UPDATE usuarios SET nombre = ?, apellido = ?, cedula = ?, ficha = ?, correo = ?, username = ?, activo = ?";
        $types = "ssssssi";
        $params = [$nombre, $apellido, $cedula, $ficha, $correo, $username, $activo];

        if ($password_set) {
            $hashed_password = password_hash($password_val, PASSWORD_DEFAULT);
            $sql_update_user .= ", password = ?";
            $types .= "s";
            $params[] = $hashed_password;
        }

        $sql_update_user .= " WHERE id_usuario = ?";
        $types .= "i";
        $params[] = $id_usuario;

        if ($stmt_update_user = $conn->prepare($sql_update_user)) {
            // Utiliza call_user_func_array para bind_param con un número dinámico de argumentos
            call_user_func_array([$stmt_update_user, 'bind_param'], array_merge([$types], $params));

            if ($stmt_update_user->execute()) {
                // Actualizar perfiles: borrar los existentes y añadir los nuevos
                $conn->begin_transaction(); // Iniciar una transacción
                try {
                    $sql_delete_profiles = "DELETE FROM usuario_perfil WHERE id_usuario = ?";
                    if ($stmt_delete_profiles = $conn->prepare($sql_delete_profiles)) {
                        $stmt_delete_profiles->bind_param("i", $id_usuario);
                        $stmt_delete_profiles->execute();
                        $stmt_delete_profiles->close();
                    } else {
                        throw new Exception("Error al preparar la eliminación de perfiles.");
                    }

                    if (!empty($selected_perfiles)) {
                        $sql_insert_user_profile = "INSERT INTO usuario_perfil (id_usuario, id_perfil) VALUES (?, ?)";
                        if ($stmt_insert_profile = $conn->prepare($sql_insert_user_profile)) {
                            foreach ($selected_perfiles as $perfil_id) {
                                $stmt_insert_profile->bind_param("ii", $id_usuario, $perfil_id);
                                $stmt_insert_profile->execute();
                            }
                            $stmt_insert_profile->close();
                        } else {
                            throw new Exception("Error al preparar la inserción de perfiles.");
                        }
                    }

                    $conn->commit(); // Confirmar la transacción
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Usuario ' . htmlspecialchars($username) . ' actualizado exitosamente.'];
                    header("location: index.php");
                    exit;

                } catch (Exception $e) {
                    $conn->rollback(); // Revertir la transacción en caso de error
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar perfiles: ' . $e->getMessage()];
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar el usuario: ' . $stmt_update_user->error];
            }
            $stmt_update_user->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al preparar la consulta de actualización: ' . $conn->error];
        }
    }
    // Si hay errores, los valores de las variables de formulario se mantendrán para rellenar los campos
} else { // Cargar los datos del usuario si es una solicitud GET (primera carga de la página)
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $id_usuario = filter_var(trim($_GET["id"]), FILTER_VALIDATE_INT);

        if ($id_usuario === false) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'ID de usuario no válido.'];
            header("location: index.php");
            exit();
        }

        // Prepara la sentencia SELECT para obtener los datos del usuario
        // ASEGÚRATE DE INCLUIR TODOS LOS CAMPOS NECESARIOS AQUÍ
        $sql_select_user = "SELECT id_usuario, nombre, apellido, cedula, ficha, correo, username, activo FROM usuarios WHERE id_usuario = ?";
        if ($stmt_select_user = $conn->prepare($sql_select_user)) {
            $stmt_select_user->bind_param("i", $param_id);
            $param_id = $id_usuario;

            if ($stmt_select_user->execute()) {
                $result = $stmt_select_user->get_result();
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    $nombre = $row['nombre'];
                    $apellido = $row['apellido'];
                    $cedula = $row['cedula'];
                    $ficha = $row['ficha'];
                    $correo = $row['correo'];
                    $username = $row['username'];
                    $activo = $row['activo']; // Carga el estado activo

                    // Obtener los perfiles actuales del usuario
                    $sql_current_perfiles = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = ?";
                    if ($stmt_current_perfiles = $conn->prepare($sql_current_perfiles)) {
                        $stmt_current_perfiles->bind_param("i", $id_usuario);
                        if ($stmt_current_perfiles->execute()) {
                            $result_current_perfiles = $stmt_current_perfiles->get_result();
                            while ($p_row = $result_current_perfiles->fetch_assoc()) {
                                $current_perfiles_ids[] = $p_row['id_perfil'];
                            }
                        }
                        $stmt_current_perfiles->close();
                    } else {
                         // Manejar error si la consulta de perfiles falla
                         error_log("Error al preparar la consulta de perfiles actuales: " . $conn->error);
                    }
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => 'No se encontró el usuario.'];
                    header("location: index.php");
                    exit();
                }
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => '¡Ups! Algo salió mal al cargar usuario.'];
                header("location: index.php");
                exit();
            }
            $stmt_select_user->close();
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Parámetro de ID de usuario no proporcionado.'];
        header("location: index.php");
        exit();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - Ferrominera SST</title>
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
        <h1>Editar Usuario</h1>
        <p>Modifique los datos del usuario y sus perfiles asignados.</p>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="data-form">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control <?php echo (!empty($nombre_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($nombre); ?>" required>
                    <span class="invalid-feedback"><?php echo $nombre_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" class="form-control <?php echo (!empty($apellido_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($apellido); ?>" required>
                    <span class="invalid-feedback"><?php echo $apellido_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" class="form-control <?php echo (!empty($cedula_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($cedula); ?>" required>
                    <span class="invalid-feedback"><?php echo $cedula_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="ficha">Ficha de Trabajo (Opcional):</label>
                    <input type="text" id="ficha" name="ficha" class="form-control <?php echo (!empty($ficha_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($ficha); ?>">
                    <span class="invalid-feedback"><?php echo $ficha_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="correo">Correo Electrónico:</label>
                    <input type="email" id="correo" name="correo" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($correo); ?>" required>
                    <span class="invalid-feedback"><?php echo $correo_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="username">Nombre de Usuario:</label>
                    <input type="text" id="username" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($username); ?>" required>
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="password">Nueva Contraseña (Dejar vacío si no desea cambiar):</label>
                    <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
            </div> <div class="form-group">
                <label>Perfiles:</label>
                <div class="checkbox-group <?php echo (!empty($perfiles_err)) ? 'is-invalid' : ''; ?>">
                    <?php foreach ($perfiles_disponibles as $perfil): ?>
                        <label class="checkbox-container">
                            <input type="checkbox" name="perfiles[]" value="<?php echo $perfil['id_perfil']; ?>"
                                <?php echo in_array($perfil['id_perfil'], $current_perfiles_ids) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            <?php echo htmlspecialchars($perfil['nombre_perfil']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <span class="invalid-feedback"><?php echo $perfiles_err; ?></span>
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
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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
            document.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function(input) {
                input.addEventListener('input', function() {
                    this.value = this.value.toUpperCase();
                });
            });
            // Replicar la funcionalidad de notificación (desde main.js) si se necesita aquí
        });
    </script>
</body>
</html>