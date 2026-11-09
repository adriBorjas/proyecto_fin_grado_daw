document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el usuario fue redirigido desde un intento de acceso
    const urlParams = new URLSearchParams(window.location.search);
    const requireLogin = urlParams.get('require_login');
    const redirectFrom = urlParams.get('redirect');
    
    if (requireLogin === 'true') {
        // Determinar el mensaje basado en el parámetro redirect
        let message = 'Para añadir productos al carrito, es necesario iniciar sesión o crear una cuenta.';
        if (redirectFrom === 'appointments') {
            message = 'Para reservar una cita, es necesario iniciar sesión o crear una cuenta.';
        }
        
        // Crear notificación personalizada
        const modal = document.createElement('div');
        modal.className = 'user-data-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Inicio de sesión requerido</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="info-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <p>${message}</p>
                    <div class="confirmation-buttons">
                        <button class="btn btn-primary" id="close-notification">Entendido</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Estilos específicos para el icono de información
        const infoIcon = modal.querySelector('.info-icon');
        if (infoIcon) {
            infoIcon.style.textAlign = 'center';
            infoIcon.style.fontSize = '48px';
            infoIcon.style.color = '#0077cc';
            infoIcon.style.margin = '20px 0';
        }
        
        // Manejar cierre del modal
        const closeBtn = modal.querySelector('.close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.remove();
            });
        }
        
        // Manejar botón de cerrar
        const closeNotificationBtn = modal.querySelector('#close-notification');
        if (closeNotificationBtn) {
            closeNotificationBtn.addEventListener('click', function() {
                modal.remove();
            });
        }
    }
});