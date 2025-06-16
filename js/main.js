/**
 * main.js
 *
 * Archivo JavaScript principal para funcionalidades de frontend.
 * Contiene funciones para mostrar notificaciones, validaciones, y lógica del menú.
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
        const type = loginErrorDiv.dataset.type || 'error';
        const message = loginErrorDiv.dataset.message || 'Ha ocurrido un error.';
        showNotification(type, message);
        loginErrorDiv.remove();
    }

    // Convertir inputs de texto a mayúsculas
    document.querySelectorAll('input[type="text"]').forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    });

    // --- PASO CLAVE: Limpiar todas las clases 'active' de los menús al cargar la página ---
    // Esto asegura que cada carga de página comience con un estado limpio,
    // y solo los elementos correctos se marquen como activos por PHP/JS.
    document.querySelectorAll('.main-nav .nav-item.active, .main-nav .submenu.active, .main-nav .submenu a.active').forEach(element => {
        element.classList.remove('active');
    });

    // --- Lógica para los Submenús Desplegables al hacer clic ---
    // Selecciona todos los enlaces que son directamente hijos de un .has-submenu
    document.querySelectorAll('.main-nav .has-submenu > .nav-link').forEach(linkItem => {
        linkItem.addEventListener('click', function(e) {
            e.preventDefault(); // Evita la navegación por defecto del enlace '#'

            const parentLi = this.closest('.has-submenu');
            const submenuUl = this.nextElementSibling; // El ul.submenu es el siguiente hermano

            if (!submenuUl || !submenuUl.classList.contains('submenu')) {
                return; // No hay submenú válido, salir
            }

            // Determina si este menú está a punto de activarse
            const isBecomingActive = !parentLi.classList.contains('active');

            // Cierra todos los otros submenús hermanos en el mismo nivel
            // y también cierra cualquier submenú anidado dentro de ellos.
            Array.from(parentLi.parentNode.children).forEach(siblingLi => {
                if (siblingLi.classList.contains('has-submenu') && siblingLi !== parentLi) {
                    siblingLi.classList.remove('active');
                    const siblingSubmenu = siblingLi.querySelector('.submenu');
                    if (siblingSubmenu) {
                        siblingSubmenu.classList.remove('active');
                        // Cierra también los sub-submenús y sus padres has-submenu dentro de este submenú que se está cerrando
                        siblingSubmenu.querySelectorAll('.has-submenu.active').forEach(nestedHasSub => {
                            nestedHasSub.classList.remove('active');
                            nestedHasSub.querySelector('.submenu').classList.remove('active');
                        });
                    }
                }
            });

            // Ahora, alternar el estado 'active' del menú clickeado
            parentLi.classList.toggle('active', isBecomingActive); // Activa si isBecomingActive es true
            submenuUl.classList.toggle('active', isBecomingActive); // Desactiva si isBecomingActive es false

            // Si se está cerrando el menú padre, asegúrate de cerrar también todos sus descendientes
            if (!isBecomingActive) { // Si el menú está siendo desactivado
                parentLi.querySelectorAll('.has-submenu.active').forEach(nestedHasSub => {
                    nestedHasSub.classList.remove('active');
                    nestedHasSub.querySelector('.submenu').classList.remove('active');
                });
            }
        });
    });

    // Cierra cualquier submenú abierto al hacer clic fuera del menú de navegación
    document.addEventListener('click', function(e) {
        // Verifica si el clic no fue dentro del área del menú de navegación
        if (!e.target.closest('.main-nav')) {
            document.querySelectorAll('.main-nav .has-submenu.active').forEach(openParentLi => {
                openParentLi.classList.remove('active');
                openParentLi.querySelectorAll('.submenu.active').forEach(openSubmenu => {
                    openSubmenu.classList.remove('active');
                    // Asegurarse de que también el li padre se desactive si es un sub-submenú
                    openSubmenu.closest('.has-submenu').classList.remove('active');
                });
            });
        }
    });

    // --- Activación de menús al cargar la página (basado en la URL) ---
    // Mantener los elementos de menú "activos" si su enlace o sub-enlace es la página actual.
    // Esto asegura que los menús desplegables se abran por defecto si su contenido está activo.
    // Esta lógica debe ejecutarse DESPUÉS de la limpieza inicial y la configuración de clics.
    document.querySelectorAll('.submenu a.active').forEach(activeSubItem => {
        let currentElement = activeSubItem;
        while (currentElement) {
            const parentSubmenu = currentElement.closest('.submenu');
            if (parentSubmenu) {
                parentSubmenu.classList.add('active');
                const parentHasSubmenu = parentSubmenu.closest('.has-submenu');
                if (parentHasSubmenu) {
                    parentHasSubmenu.classList.add('active');
                }
                currentElement = parentHasSubmenu; // Sube al siguiente nivel (al parent has-submenu)
            } else {
                currentElement = null; // Detener si no hay más submenús padres
            }
        }
    });
});