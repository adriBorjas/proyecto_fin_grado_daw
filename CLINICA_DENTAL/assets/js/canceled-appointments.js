document.addEventListener('DOMContentLoaded', function() {
    console.log('Script de citas canceladas cargado');
    
    // Verificar inmediatamente si hay el parámetro en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('show_canceled') === '1') {
        console.log('Detectado parámetro show_canceled=1, mostrando modal...');
        setTimeout(showCanceledAppointmentsModal, 500); // Pequeño retraso para asegurar que todo está cargado
        
        // Limpiar el parámetro de la URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function showCanceledAppointmentsModal() {
        console.log('Intentando obtener citas canceladas...');
        // Obtener datos de citas canceladas desde la sesión
        fetch(SITE_URL + '/api/appointments/get_canceled_appointments.php')
            .then(response => {
                console.log('Respuesta recibida, status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Respuesta de get_canceled_appointments.php:', data);
                if (data.success && data.appointments && data.appointments.length > 0) {
                    console.log('Se encontraron citas canceladas:', data.appointments.length);
                    createCanceledAppointmentsModal(data.appointments);
                } else {
                    console.log('No hay citas canceladas o la respuesta no tiene el formato esperado');
                }
            })
            .catch(error => {
                console.error('Error al cargar citas canceladas:', error);
            });
    }

    // Modificar la función createCanceledAppointmentsModal
    function createCanceledAppointmentsModal(appointments) {
        let appointmentsHtml = '';
        
        // Definir aquí la variable appointmentIds que falta
        const appointmentIds = appointments.map(appointment => appointment.id);
        
        appointments.forEach(appointment => {
            const fechaCita = new Date(appointment.fecha).toLocaleDateString('es-ES', {
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            });
            
            const horaCita = appointment.hora_inicio ? appointment.hora_inicio.substring(0, 5) : '00:00';
            
            // Usar directamente el motivo de cancelación de la cita
            const razon = appointment.razon_cancelacion || 'Horario no disponible';
            
            console.log('Mostrando motivo de cancelación:', razon);
            
            appointmentsHtml += `
                <div class="canceled-appointment-item">
                    <div class="appointment-content">
                        <div class="appointment-info-box">
                            <p class="appointment-message">Tu cita ha sido cancelada por: <strong>${razon}</strong></p>
                            <p class="appointment-date">Fecha: ${fechaCita} a las ${horaCita}</p>
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Crear el modal
        const modal = document.createElement('div');
        modal.className = 'user-data-modal canceled-appointments-modal';
        modal.style.zIndex = '2000'; // Asegurar que aparezca por encima de todo
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Información sobre tus citas</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <p class="modal-intro">Las siguientes citas han sido canceladas:</p>
                    <div class="canceled-appointments-container">
                        ${appointmentsHtml}
                    </div>
                    <p class="modal-action">Por favor, reserva nuevas citas desde tu perfil.</p>
                    <div class="confirmation-buttons">
                        <button class="btn btn-primary" id="understand-button">
                            <i class="fas fa-check-circle"></i> Entendido
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        console.log('Modal de citas canceladas creado y añadido al DOM');
        
        // Manejar el cierre del modal
        const closeBtn = modal.querySelector('.close-modal');
        closeBtn.addEventListener('click', function() {
            eliminarCitasNotificadas(appointmentIds);
            modal.remove();
        });
        
        const understandBtn = modal.querySelector('#understand-button');
        understandBtn.addEventListener('click', function() {
            // Mostrar indicador de carga
            understandBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            understandBtn.disabled = true;
            
            // Modificar esta parte para esperar la respuesta del fetch
            fetch(SITE_URL + '/api/appointments/delete_notified_appointments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ appointment_ids: appointmentIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Citas canceladas eliminadas correctamente:', data.rows_affected);
                    // Cerrar el modal solo cuando la operación haya terminado
                    modal.remove();
                } else {
                    console.error('Error al eliminar citas canceladas:', data.message);
                    // Restaurar el botón para permitir otro intento
                    understandBtn.innerHTML = 'Entendido';
                    understandBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error en la solicitud de eliminación:', error);
                // Restaurar el botón para permitir otro intento
                understandBtn.innerHTML = 'Entendido';
                understandBtn.disabled = false;
            });
        });
    }
    
    // Asegurar que esta función se ejecuta correctamente cuando el usuario hace clic en "Entendido"
    function eliminarCitasNotificadas(appointmentIds) {
        if (!appointmentIds || appointmentIds.length === 0) {
            console.log('No hay IDs de citas para eliminar');
            return;
        }
        
        console.log('Eliminando citas con IDs:', appointmentIds);
        fetch(SITE_URL + '/api/appointments/delete_notified_appointments.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ appointment_ids: appointmentIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Citas canceladas eliminadas correctamente:', data.rows_affected);
            } else {
                console.error('Error al eliminar citas canceladas:', data.message);
            }
        })
        .catch(error => {
            console.error('Error en la solicitud de eliminación:', error);
        });
    }
});