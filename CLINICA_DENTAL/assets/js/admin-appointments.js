// Funciones específicas para la gestión de citas por parte del administrador
document.addEventListener('DOMContentLoaded', function() {
    // Agregar evento a botones de cancelación de citas
    const cancelButtons = document.querySelectorAll('.btn-cancel-admin-appointment');
    if (cancelButtons) {
        cancelButtons.forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.getAttribute('data-id');
                cancelAppointmentAsAdmin(appointmentId);
            });
        });
    }
});

// Función para cancelar cita como administrador
function cancelAppointmentAsAdmin(appointmentId) {
    // Crear un modal de confirmación
    const modal = document.createElement('div');
    modal.className = 'user-data-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cancelar cita de usuario</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cancelar esta cita?</p>
                
                <div class="reason-selector-group">
                    <label for="razon-cancelacion">Seleccione el motivo de cancelación:</label>
                    <select id="razon-cancelacion" class="reason-selector">
                        <option value="" disabled selected>Seleccione una razón</option>
                        <option value="Vacaciones" class="reason-option reason-option-vacation">
                            📅 Vacaciones
                        </option>
                        <option value="Enfermedad" class="reason-option reason-option-illness">
                            🏥 Enfermedad
                        </option>
                    </select>
                    <div id="razon-error" class="reason-error">
                        Debe seleccionar una razón para cancelar la cita
                    </div>
                </div>
                
                <div class="confirmation-buttons">
                    <button class="btn btn-secondary" id="cancel-action">No, mantener</button>
                    <button class="btn btn-danger" id="confirm-action">Sí, cancelar</button>
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
    
    // Manejar botón cancelar
    const cancelBtn = modal.querySelector('#cancel-action');
    cancelBtn.addEventListener('click', function() {
        modal.remove();
    });
    
    // Manejar botón confirmar
    const confirmBtn = modal.querySelector('#confirm-action');
    confirmBtn.addEventListener('click', function() {
        // Validar que se haya seleccionado una razón
        const razonSelect = document.getElementById('razon-cancelacion');
        const razonError = document.getElementById('razon-error');
        
        if (!razonSelect.value) {
            razonError.classList.add('visible');
            razonSelect.focus();
            return;
        }
        
        // Mostrar indicador de carga
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        confirmBtn.disabled = true;
        
        // Enviar solicitud para cancelar la cita
        fetch(SITE_URL + '/api/appointments/admin_cancel_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                appointment_id: appointmentId,
                razon: razonSelect.value
            })
        })
        .then(response => response.json())
        .then(data => {
            modal.remove();
            
            if (data.success) {
                // Mostrar mensaje de éxito
                const successModal = document.createElement('div');
                successModal.className = 'user-data-modal';
                successModal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Éxito</h3>
                            <span class="close-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <div class="success-message-container">
                                <i class="fas fa-check-circle success-icon"></i>
                                <p>Cita cancelada correctamente y el usuario será notificado.</p>
                            </div>
                            <div class="confirmation-buttons">
                                <button class="btn btn-primary" id="ok-button">Aceptar</button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(successModal);
                
                // Cerrar el modal de éxito
                const closeSuccessBtn = successModal.querySelector('.close-modal');
                closeSuccessBtn.addEventListener('click', function() {
                    successModal.remove();
                    location.reload(); // Recargar para actualizar la lista
                });
                
                const okBtn = successModal.querySelector('#ok-button');
                okBtn.addEventListener('click', function() {
                    successModal.remove();
                    location.reload(); // Recargar para actualizar la lista
                });
            } else {
                // Mostrar error
                alert(data.message || 'Error al cancelar la cita');
            }
        })
        .catch(error => {
            modal.remove();
            console.error('Error:', error);
            alert('Error al cancelar la cita. Por favor, intenta de nuevo.');
        });
    });
    
    // Quitar mensaje de error cuando se selecciona una opción
    const razonSelect = document.getElementById('razon-cancelacion');
    razonSelect.addEventListener('change', function() {
        const razonError = document.getElementById('razon-error');
        razonError.classList.remove('visible');
    });
}