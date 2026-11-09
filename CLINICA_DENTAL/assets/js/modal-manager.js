/**
 * Sistema de gestión de modales
 */

const ModalManager = {
    activeModals: [],
    
    // Crear un nuevo modal
    createModal: function(options) {
        const modalId = 'modal-' + Math.random().toString(36).substr(2, 9);
        
        const modal = document.createElement('div');
        modal.className = 'user-data-modal';
        modal.id = modalId;
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${options.title || 'Mensaje'}</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    ${options.icon ? `<div class="${options.icon}-icon"><i class="fas fa-${options.icon}"></i></div>` : ''}
                    <p>${options.message}</p>
                    <div class="confirmation-buttons">
                        ${options.showCancel ? `<button class="btn btn-secondary" id="cancel-action">${options.cancelText || 'Cancelar'}</button>` : ''}
                        <button class="btn btn-${options.type || 'primary'}" id="confirm-action">${options.confirmText || 'Aceptar'}</button>
                    </div>
                </div>
            </div>
        `;
        
        // Añadir el modal al DOM
        document.body.appendChild(modal);
        this.activeModals.push({
            id: modalId,
            element: modal,
            options: options
        });
        
        // Manejar cierre del modal
        const closeBtn = modal.querySelector('.close-modal');
        closeBtn.addEventListener('click', () => this.closeModal(modalId, 'cancel'));
        
        // Manejar botón de cancelar
        const cancelBtn = modal.querySelector('#cancel-action');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeModal(modalId, 'cancel'));
        }
        
        // Manejar botón de confirmar
        const confirmBtn = modal.querySelector('#confirm-action');
        confirmBtn.addEventListener('click', () => this.closeModal(modalId, 'confirm'));
        
        // Auto-cerrar después de cierto tiempo si se solicita
        if (options.autoClose && options.autoCloseTime) {
            setTimeout(() => {
                if (this.isModalActive(modalId)) {
                    this.closeModal(modalId, 'auto');
                }
            }, options.autoCloseTime);
        }
        
        return modalId;
    },
    
    // Cerrar un modal específico
    closeModal: function(modalId, action) {
        const modalIndex = this.activeModals.findIndex(m => m.id === modalId);
        if (modalIndex === -1) return;
        
        const modalData = this.activeModals[modalIndex];
        const element = modalData.element;
        const options = modalData.options;
        
        // Ejecutar callbacks según la acción
        if (action === 'confirm' && typeof options.onConfirm === 'function') {
            options.onConfirm();
        } else if (action === 'cancel' && typeof options.onCancel === 'function') {
            options.onCancel();
        } else if (action === 'auto' && typeof options.onAutoClose === 'function') {
            options.onAutoClose();
        }
        
        // Eliminar elemento del DOM con animación
        element.classList.add('fade-out');
        setTimeout(() => {
            if (document.body.contains(element)) {
                element.remove();
            }
            this.activeModals.splice(modalIndex, 1);
        }, 300);
    },
    
    // Cerrar todos los modales
    closeAllModals: function() {
        [...this.activeModals].forEach(modal => {
            this.closeModal(modal.id, 'force');
        });
    },
    
    // Verificar si un modal está activo
    isModalActive: function(modalId) {
        return this.activeModals.some(m => m.id === modalId);
    },
    
    // Mostrar notificación de éxito
    showSuccess: function(message, autoClose = true) {
        return this.createModal({
            title: 'Operación Exitosa',
            message: message,
            icon: 'check-circle',
            type: 'primary',
            confirmText: 'Aceptar',
            autoClose: autoClose,
            autoCloseTime: 5000
        });
    },
    
    // Mostrar notificación de error
    showError: function(message) {
        return this.createModal({
            title: 'Error',
            message: message,
            icon: '', // Eliminar icono
            type: 'danger',
            confirmText: 'Entendido'
        });
    },
    
    // Mostrar confirmación
    showConfirmation: function(options) {
        return this.createModal({
            title: options.title || 'Confirmar Acción',
            message: options.message,
            icon: options.icon || 'question-circle',
            type: options.type || 'primary',
            confirmText: options.confirmText || 'Confirmar',
            cancelText: options.cancelText || 'Cancelar',
            showCancel: true,
            onConfirm: options.onConfirm,
            onCancel: options.onCancel
        });
    }
};