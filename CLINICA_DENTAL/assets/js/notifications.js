// Funciones globales para mostrar notificaciones y modales personalizados

// Función para mostrar notificación de éxito
function showSuccessNotification(message, autoClose = true) {
    // Crear el modal
    const modal = document.createElement('div');
    modal.className = 'user-data-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Operación Exitosa</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <p>${message}</p>
                <div class="confirmation-buttons">
                    <button class="btn btn-primary" id="close-notification">Aceptar</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Manejar cierre del modal
    const closeBtn = modal.querySelector('.close-modal');
    closeBtn.addEventListener('click', function() {
        modal.remove();
    });
    
    // Manejar botón de cerrar
    const closeNotificationBtn = modal.querySelector('#close-notification');
    closeNotificationBtn.addEventListener('click', function() {
        modal.remove();
    });
    
    // Auto-cerrar después de 5 segundos si se solicita
    if (autoClose) {
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
}

// Función para mostrar notificación de error
function showErrorNotification(message) {
    // Crear el modal
    const modal = document.createElement('div');
    modal.className = 'user-data-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Error</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>${message}</p>
                <div class="confirmation-buttons">
                    <button class="btn btn-primary" id="close-error">Entendido</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Manejar cierre del modal
    const closeBtn = modal.querySelector('.close-modal');
    closeBtn.addEventListener('click', function() {
        modal.remove();
    });
    
    // Manejar botón de cerrar
    const closeErrorBtn = modal.querySelector('#close-error');
    closeErrorBtn.addEventListener('click', function() {
        modal.remove();
    });
}

// Función para mostrar confirmación personalizada
function showConfirmationModal(options) {
    // Crear el modal
    const modal = document.createElement('div');
    modal.className = 'user-data-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${options.title || 'Confirmar Acción'}</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>${options.message}</p>
                <div class="confirmation-buttons">
                    <button class="btn btn-secondary" id="cancel-action">${options.cancelText || 'Cancelar'}</button>
                    <button class="btn btn-${options.type || 'primary'}" id="confirm-action">${options.confirmText || 'Confirmar'}</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Manejar cierre del modal
    const closeBtn = modal.querySelector('.close-modal');
    closeBtn.addEventListener('click', function() {
        modal.remove();
        if (typeof options.onCancel === 'function') {
            options.onCancel();
        }
    });
    
    // Manejar botón de cancelar
    const cancelBtn = modal.querySelector('#cancel-action');
    cancelBtn.addEventListener('click', function() {
        modal.remove();
        if (typeof options.onCancel === 'function') {
            options.onCancel();
        }
    });
    
    // Manejar botón de confirmar
    const confirmBtn = modal.querySelector('#confirm-action');
    confirmBtn.addEventListener('click', function() {
        modal.remove();
        if (typeof options.onConfirm === 'function') {
            options.onConfirm();
        }
    });
}

// Función para permitir solo teclas numéricas
function isNumberKey(evt) {
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}