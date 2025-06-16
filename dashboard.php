<?php
// dashboard.php - Página principal del sistema (Dashboard)

require_once __DIR__ . '/includes/session_check.php'; // Verifica sesión y permisos

// Para depuración: Muestra el contenido de la sesión.
// Puedes QUITAR estas líneas una vez que confirmes que el menú funciona.
// echo '<pre style="background-color: #f0f0f0; padding: 10px; border: 1px solid #ccc;">';
// echo 'Contenido de $_SESSION:<br>';
// print_r($_SESSION);
// echo '</pre>';

// Si necesitas datos de la base de datos para el dashboard:
// require_once __DIR__ . '/includes/db_connection.php';
// ... lógica para obtener datos del dashboard ...
// $conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ferrominera SST</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
</head>
<body>
    <?php require_once __DIR__ . '/includes/main_header.php'; // Incluye el nuevo menú ?>

    <main class="content">
        <div class="container">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            <p>Este es el panel principal de control del sistema Ferrominera SST.</p>
            <p>Tus perfiles actuales: <?php echo implode(', ', $_SESSION['perfiles']); ?></p>

            <div class="dashboard-widgets">
                <?php
                // Estos bloques de código se eliminan o se adaptan para no duplicar el menú.
                // Si deseas mostrar resúmenes o accesos rápidos *distintos* a los del menú,
                // aquí es donde los colocarías, pero no los enlaces directos de navegación
                // que ya están en el main_header.php
                ?>
                <?php if (in_array('Administrador', $_SESSION['perfiles'])): ?>
                    <div class="widget">
                        <h2>Panel Administrativo</h2>
                        <p>Accede a la configuración del sistema y mantenimiento de datos maestros desde el menú de navegación superior.</p>
                        </div>
                <?php endif; ?>

                <?php if (in_array('Operador', $_SESSION['perfiles'])): ?>
                    <div class="widget">
                        <h2>Acciones Operativas</h2>
                        <ul>
                            <li><a href="#">Registrar Condición Insegura</a></li>
                            <li><a href="#">Reportar Accidente de Trabajo</a></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if (in_array('Consultor', $_SESSION['perfiles'])): ?>
                    <div class="widget">
                        <h2>Informes y Consultas</h2>
                        <ul>
                            <li><a href="#">Ver Reporte de Condiciones Inseguras</a></li>
                            <li><a href="#">Consultar Estadísticas de Accidentes</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php // require_once __DIR__ . '/includes/footer.php'; // Si tienes un pie de página compartido ?>
    <script src="js/main.js"></script>
</body>
</html>