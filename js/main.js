/**
 * main.js
 *
 * Archivo JavaScript principal para funcionalidades de frontend.
 * Contiene funciones para mostrar notificaciones, validaciones, etc.
 */

// Función para mostrar notificaciones personalizadas.
// type: 'success', 'error', 'warning', 'info'
// message: El texto del mensaje a mostrar.
// duration: Duración en milisegundos que el mensaje estará visible.
function showNotification(type, message, duration = 5000) {
    const notificationContainer = document.createElement('div');
    notificationContainer.classList.add('notification-message', type);
    notificationContainer.textContent = message;

    document.body.appendChild(notificationContainer);

    // Muestra la notificación con un pequeño retraso para el efecto de transición.
    setTimeout(() => {
        notificationContainer.classList.add('show');
    }, 100);

    // Oculta la notificación después de la duración especificada.
    setTimeout(() => {
        notificationContainer.classList.remove('show');
        // Elimina la notificación del DOM después de que la transición haya terminado.
        notificationContainer.addEventListener('transitionend', () => {
            notificationContainer.remove();
        }, { once: true });
    }, duration);
}

// Escucha cuando el DOM está completamente cargado.
document.addEventListener('DOMContentLoaded', function() {
    // Si hay un error de login, lo muestra como notificación.
    const loginErrorDiv = document.getElementById('loginErrorNotification');
    if (loginErrorDiv) {
        // Obtenemos el texto del error
        const errorMessage = loginErrorDiv.textContent;
        // Mostramos la notificación de error
        showNotification('error', errorMessage, 6000); // Duración de 6 segundos para el login
        // Removemos el div del DOM para evitar duplicidad y mantener el control con la función de notificación
        loginErrorDiv.remove();
    }

    // Convertir todos los inputs de texto a mayúsculas al escribir
    document.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function(input) {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });
});

// ... (código existente de showNotification y DOMContentLoaded) ...

// Lógica para los submenús desplegables
document.addEventListener('DOMContentLoaded', function() {
    // ... (código existente de notificaciones y inputs a mayúsculas) ...

    document.querySelectorAll('.has-submenu > .nav-link').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault(); // Evita la navegación por defecto
            const submenu = this.nextElementSibling;
            if (submenu && submenu.classList.contains('submenu')) {
                // Cierra otros submenús antes de abrir uno nuevo
                document.querySelectorAll('.submenu.active').forEach(openSubmenu => {
                    if (openSubmenu !== submenu) {
                        openSubmenu.classList.remove('active');
                    }
                });
                submenu.classList.toggle('active');
            }
        });
    });

    // Cierra cualquier submenú abierto al hacer clic fuera de él
    document.addEventListener('click', function(e) {
        // Verifica si el clic no fue dentro de un elemento con submenu
        if (!e.target.closest('.has-submenu')) {
            document.querySelectorAll('.submenu.active').forEach(openSubmenu => {
                openSubmenu.classList.remove('active');
            });
        }
    });
});