/**
 * Script para mostrar notificaciones personalizadas de autenticación
 */
document.addEventListener('DOMContentLoaded', function() {
    // Obtener parámetros de URL
    const urlParams = new URLSearchParams(window.location.search);
    const notificationType = urlParams.get('notification');
    
    // Si no hay notificaciones que mostrar, salir
    if (!notificationType) return;
    
    // Determinar mensaje y título según el tipo de notificación
    let title, message, icon;
    
    if (notificationType === 'login') {
        title = '¡Bienvenido de nuevo!';
        message = `<p>Has iniciado sesión correctamente.</p>
                  <p>¡Nos alegra verte de nuevo!</p>`;
        icon = 'sign-in-alt';
    } else if (notificationType === 'register') {
        title = '¡Cuenta creada con éxito!';
        message = `<p>Tu cuenta ha sido creada correctamente.</p>
                  <p>¡Bienvenido a nuestra comunidad!</p>`;
        icon = 'user-plus';
    } else {
        // Si el tipo no es reconocido, salir
        return;
    }
    
    // Mostrar la notificación personalizada
    showAuthNotification(title, message, icon);
    
    // Limpiar el parámetro de la URL para que no se muestre de nuevo al refrescar
    if (window.history && window.history.replaceState) {
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]notification=[^&]+/, '');
        window.history.replaceState({}, document.title, newUrl);
    }
});

/**
 * Muestra una notificación personalizada de autenticación
 */
function showAuthNotification(title, message, icon) {
    // Crear el modal
    const modal = document.createElement('div');
    modal.className = 'user-data-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="auth-icon">
                    <i class="fas fa-${icon}"></i>
                </div>
                ${message}
                <div class="confirmation-buttons">
                    <button class="btn btn-primary" id="close-auth-notification">Continuar</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Estilos específicos para el icono
    const authIcon = modal.querySelector('.auth-icon');
    if (authIcon) {
        authIcon.style.textAlign = 'center';
        authIcon.style.fontSize = '48px';
        authIcon.style.color = '#0077cc';
        authIcon.style.margin = '20px 0';
    }
    
    // Manejar cierre del modal
    const closeBtn = modal.querySelector('.close-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.remove();
        });
    }
    
    // Manejar botón de cerrar
    const closeNotificationBtn = modal.querySelector('#close-auth-notification');
    if (closeNotificationBtn) {
        closeNotificationBtn.addEventListener('click', function() {
            modal.remove();
        });
    }
    
    // Auto-cerrar después de 5 segundos
    setTimeout(() => {
        if (document.body.contains(modal)) {
            modal.classList.add('fade-out');
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    modal.remove();
                }
            }, 500);
        }
    }, 5000);
}