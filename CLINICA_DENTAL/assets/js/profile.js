// Funciones específicas para el perfil del usuario
document.addEventListener('DOMContentLoaded', function() {
    // Manejar formulario de actualización de perfil
    const profileForm = document.getElementById('profile-form');
    const messageContainer = document.getElementById('update-messages');
    
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('/CLINICA_DENTAL/api/user/update-profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage('Perfil actualizado correctamente.');
                } else {
                    showErrorMessage(data.message || 'Error al actualizar el perfil.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('Error en la comunicación con el servidor. Por favor, intenta más tarde.');
            });
        });
    }
    
    // Funciones para mostrar mensajes
    function showSuccessMessage(message) {
        if (messageContainer) {
            messageContainer.innerHTML = `<div class="success-message">${message}</div>`;
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 5000);
        }
    }
    
    function showErrorMessage(message) {
        if (messageContainer) {
            messageContainer.innerHTML = `<div class="error-message">${message}</div>`;
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 5000);
        }
    }
    
    function isNumberKey(evt) {
        const charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;
        return true;
    }

    // Cargar las citas del usuario en el perfil
    loadUserAppointmentsInProfile();
});

// Función para cargar las citas del usuario en el perfil
function loadUserAppointmentsInProfile() {
    const appointmentsList = document.getElementById('profile-appointments-list');
    if (!appointmentsList) return;
    
    fetch(SITE_URL + '/api/appointments/get_user_appointments.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                appointmentsList.innerHTML = `<div class="error-message">${data.message}</div>`;
                return;
            }
            
            const appointments = data.appointments;
            
            if (appointments.length === 0) {
                appointmentsList.innerHTML = '<div class="no-appointments-message">No tienes citas programadas.</div>';
                return;
            }
            
            // Mostrar todas las citas del usuario
            let html = '';
            
            appointments.forEach(appointment => {
                const formattedDate = new Date(appointment.fecha).toLocaleDateString('es-ES', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                const timeStart = appointment.hora_inicio.substring(0, 5);
                const timeEnd = appointment.hora_fin.substring(0, 5);
                
                html += `
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <div class="appointment-date">${formattedDate}</div>
                        </div>
                        <div class="appointment-details">
                            <div class="appointment-detail">
                                <div class="appointment-detail-label">Hora:</div>
                                <div>${timeStart} - ${timeEnd}</div>
                            </div>
                        </div>
                        <div class="appointment-actions">
                            <button class="btn-cancel-appointment" data-id="${appointment.id}">
                                <i class="fas fa-times-circle"></i> Cancelar cita
                            </button>
                        </div>
                    </div>
                `;
            });
            
            appointmentsList.innerHTML = html;
            
            // Agregar manejadores de eventos para los botones de cancelación
            document.querySelectorAll('.btn-cancel-appointment').forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.getAttribute('data-id');
                    cancelAppointmentFromProfile(appointmentId);
                });
            });
        })
        .catch(error => {
            console.error('Error:', error);
            if (appointmentsList) {
                appointmentsList.innerHTML = '<div class="error-message">Error al cargar las citas.</div>';
            }
        });
}

// Función para cancelar cita desde el perfil
function cancelAppointmentFromProfile(appointmentId) {
    // Crear un modal de confirmación
    const modal = document.createElement('div');
    modal.className = 'user-data-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>Cancelar cita</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas cancelar esta cita?</p>
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
        // Mostrar indicador de carga
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        confirmBtn.disabled = true;
        
        // Enviar solicitud para cancelar la cita
        fetch(SITE_URL + '/api/appointments/cancel_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                appointment_id: appointmentId
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
                            <p>Cita cancelada correctamente.</p>
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
                });
                
                const okBtn = successModal.querySelector('#ok-button');
                okBtn.addEventListener('click', function() {
                    successModal.remove();
                });
                
                // Recargar las citas para actualizar la lista
                setTimeout(function() {
                    location.reload();
                }, 1000);
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
}