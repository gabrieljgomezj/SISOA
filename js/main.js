/**
 * main.js
 *
 * Archivo JavaScript principal para funcionalidades de frontend.
 * Contiene funciones para mostrar notificaciones, validaciones, etc.
 * La lógica del menú de navegación se ha movido a main_header.php.
 */

// Función para mostrar notificaciones personalizadas.
// type: 'success', 'error', 'warning', 'info'
// message: El texto del mensaje a mostrar.
// duration: Duración en milisegundos que el mensaje estará visible.
function showNotification(type, message, duration = 5000) {
    // Evitar duplicar notificaciones si ya hay una similar
    const existingNotification = document.querySelector(`.notification-message.${type}`);
    if (existingNotification && existingNotification.textContent === message) {
        return;
    }

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
        // Asegúrate de que el mensaje y el tipo se pasen correctamente desde PHP
        // Por ejemplo, <div id="loginErrorNotification" data-type="error" data-message="Mensaje de error"></div>
        const type = loginErrorDiv.dataset.type || 'error';
        const message = loginErrorDiv.dataset.message || 'Ha ocurrido un error inesperado.';
        showNotification(type, message);
        loginErrorDiv.remove(); // Elimina el div una vez mostrado
    }

    // Convertir automáticamente a mayúsculas los campos con la clase 'uppercase-input'
    document.querySelectorAll('.uppercase-input').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // NOTA: La lógica del menú de navegación (desplegables) se ha movido a main_header.php
    // para evitar duplicidad y asegurar que se cargue con el HTML del menú.
});