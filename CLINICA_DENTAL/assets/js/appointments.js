let SITE_URL = '';
document.addEventListener('DOMContentLoaded', function() {
    // Obtener SITE_URL de una meta etiqueta que debemos añadir al header
    const siteUrlMeta = document.querySelector('meta[name="site-url"]');
    if (siteUrlMeta) {
        SITE_URL = siteUrlMeta.getAttribute('content');
    } else {
        // Fallback al path actual
        SITE_URL = window.location.origin + '/CLINICA_DENTAL';
    }
    
    // Referencias a elementos DOM
    const calendar = document.getElementById('appointments-calendar');
    const selectedDateDisplay = document.getElementById('selected-date-display');
    const timeSlots = document.getElementById('time-slots');
    const userAppointmentsList = document.getElementById('user-appointments-list');
    const reserveModal = document.getElementById('reserve-modal');
    
    // Estado de la aplicación
    let selectedDate = null;
    
    // Inicializar el calendario con Flatpickr
    const calendarInstance = flatpickr(calendar, {
        inline: true,
        locale: "es",
        minDate: "today",
        maxDate: new Date().fp_incr(60), // Permite reservar hasta 60 días en el futuro
        disable: [
            function(date) {
                // Deshabilitar sábados y domingos (0 es domingo, 6 es sábado)
                return (date.getDay() === 0 || date.getDay() === 6);
            }
        ],
        onChange: function(selectedDates, dateStr) {
            selectedDate = dateStr;
            selectedDateDisplay.textContent = `Horarios para el ${formatDate(selectedDate)}`;
            loadTimeSlots(selectedDate);
        }
    });
    
    // Verificar si hay una cita pendiente y el usuario acaba de registrarse
    const pendingDate = localStorage.getItem('pending_appointment_date');
    const pendingTime = localStorage.getItem('pending_appointment_time');
    const registerSuccess = new URLSearchParams(window.location.search).get('register_success') === 'true';

    if (pendingDate && pendingTime && registerSuccess) {
        // Eliminar datos pendientes del localStorage
        localStorage.removeItem('pending_appointment_date');
        localStorage.removeItem('pending_appointment_time');
        
        // Seleccionar la fecha en el calendario
        calendarInstance.setDate(pendingDate);
        
        // Cargar horarios disponibles para esa fecha
        loadTimeSlots(pendingDate);
        
        // Mostrar mensaje para que el usuario vuelva a seleccionar el horario
        showSuccessNotification('Cuenta creada exitosamente. Por favor, selecciona nuevamente el horario para tu cita.');
        
        // Limpiar parámetros de URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // Cargar las citas del usuario al iniciar
    loadUserAppointments();
    
    // Función para formatear fechas en formato legible
    function formatDate(dateStr) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-ES', options);
    }
    
    // Función para cargar los horarios disponibles según la fecha seleccionada
    function loadTimeSlots(date = '') {
        // Eliminar mensaje de carga
        timeSlots.innerHTML = ''; // Ya no mostramos "Cargando horarios disponibles..."
        
        fetch(`${SITE_URL}/api/appointments/get_time_slots.php?date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderTimeSlots(data.timeSlots);
                } else {
                    timeSlots.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                timeSlots.innerHTML = '<div class="error-message">Error al cargar los horarios. Por favor, intenta de nuevo.</div>';
            });
    }
    
    // Función para renderizar los horarios disponibles
    function renderTimeSlots(slots) {
        if (slots.length === 0) {
            timeSlots.innerHTML = '<div class="no-slots-message"><i class="far fa-calendar-times"></i> No hay horarios disponibles para esta fecha.</div>';
            return;
        }
        
        let html = '';
        
        slots.forEach(slot => {
            const isAvailable = slot.available;
            const slotClass = isAvailable ? 'available' : 'unavailable';
            const statusText = isAvailable ? 'Disponible' : 'No disponible';
            
            html += `
                <div class="time-slot ${slotClass}">
                    <div class="time-slot-time">${formatTime(slot.hora_inicio)} - ${formatTime(slot.hora_fin)}</div>
                    <div class="time-slot-status">${statusText}</div>
                    <button 
                        class="btn-reserve" 
                        data-date="${selectedDate}" 
                        data-time="${slot.hora_inicio}" 
                        data-end="${slot.hora_fin}"
                        ${!isAvailable ? 'disabled' : ''}
                    >
                        Reservar
                    </button>
                </div>
            `;
        });
        
        timeSlots.innerHTML = html;
        
        // Modificar el evento de los botones de reserva
        document.querySelectorAll('.btn-reserve').forEach(button => {
            button.addEventListener('click', function() {
                const date = this.getAttribute('data-date');
                const time = this.getAttribute('data-time');
                const endTime = this.getAttribute('data-end');
                
                // Formatear la fecha para mostrarla en el modal
                const formattedDate = formatDate(date);
                const formattedTime = `${formatTime(time)} - ${formatTime(endTime)}`;
                
                // Crear un modal de confirmación más atractivo
                const modal = document.createElement('div');
                modal.className = 'user-data-modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Confirmar tu cita</h3>
                            <span class="close-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <div class="appointment-details-preview">
                                <div class="appointment-preview-item">
                                    <span class="preview-label">Fecha:</span>
                                    <span class="preview-value">${formattedDate}</span>
                                </div>
                                <div class="appointment-preview-item">
                                    <span class="preview-label">Hora:</span>
                                    <span class="preview-value">${formattedTime}</span>
                                </div>
                            </div>
                            <p class="confirmation-text">¿Confirmas la reserva de esta cita?</p>
                            <div class="confirmation-buttons">
                                <button class="btn btn-secondary" id="cancel-confirm">Cancelar</button>
                                <button class="btn btn-primary" id="confirm-appointment">Confirmar cita</button>
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Guardar los valores para usarlos al confirmar
                const appointmentData = {
                    date: date,
                    time: time
                };
                
                // Manejar el cierre del modal
                const closeBtn = modal.querySelector('.close-modal');
                closeBtn.addEventListener('click', function() {
                    modal.remove();
                });
                
                // Manejar el botón cancelar
                const cancelBtn = modal.querySelector('#cancel-confirm');
                cancelBtn.addEventListener('click', function() {
                    modal.remove();
                });
                
                // Manejar el botón confirmar
                const confirmBtn = modal.querySelector('#confirm-appointment');
                confirmBtn.addEventListener('click', function() {
                    // Mostrar indicador de carga
                    confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                    confirmBtn.disabled = true;
                    
                    // Enviar la solicitud para crear la cita
                    fetch(`${SITE_URL}/api/appointments/create_appointment.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(appointmentData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        modal.remove();
                        
                        if (data.success) {
                            showSuccessNotification('¡Cita reservada correctamente!');
                            loadUserAppointments();
                            if (selectedDate) {
                                loadTimeSlots(selectedDate);
                            }
                        } else {
                            showErrorNotification(data.message || 'Error al reservar la cita');
                        }
                    })
                    .catch(error => {
                        modal.remove();
                        console.error('Error:', error);
                        showErrorNotification('Error al reservar la cita. Por favor, intenta de nuevo.');
                    });
                });
            });
        });
    }
    
    // Formatear hora (de HH:MM:SS a HH:MM)
    function formatTime(timeStr) {
        return timeStr.substring(0, 5);
    }
    
    // Función para cargar las citas del usuario
    function loadUserAppointments() {
        // Eliminar mensaje de carga
        userAppointmentsList.innerHTML = ''; // Ya no mostramos "Cargando tus citas..."
        
        fetch(`${SITE_URL}/api/appointments/get_user_appointments.php`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderUserAppointments(data.appointments);
                } else {
                    userAppointmentsList.innerHTML = `<div class="error-message">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                userAppointmentsList.innerHTML = '<div class="error-message">Error al cargar tus citas. Por favor, intenta de nuevo.</div>';
            });
    }
    
    // Función para renderizar las citas del usuario (sin motivo ni estado)
    function renderUserAppointments(appointments) {
        const appointmentsList = document.getElementById('user-appointments-list');
        
        if (!appointmentsList) return;
        
        if (appointments.length === 0) {
            appointmentsList.innerHTML = '<p class="no-appointments">No tienes citas programadas.</p>';
            return;
        }
        
        let html = '';
        
        appointments.forEach(appointment => {
            const formattedDate = formatDate(appointment.fecha);
            const formattedTime = `${formatTime(appointment.hora_inicio)} - ${formatTime(appointment.hora_fin)}`;
            
            html += `
                <div class="appointment-card">
                    <div class="appointment-header">
                        <div class="appointment-date">${formattedDate}</div>
                    </div>
                    <div class="appointment-details">
                        <div class="appointment-detail">
                            <div class="appointment-detail-label">Hora:</div>
                            <div>${formattedTime}</div>
                        </div>
                    </div>
                    <div class="appointment-actions">
                        <button class="btn-cancel-appointment" data-id="${appointment.id}">Cancelar cita</button>
                    </div>
                </div>
            `;
        });
        
        appointmentsList.innerHTML = html;
        
        // Añadir eventos a los botones de cancelar cita
        document.querySelectorAll('.btn-cancel-appointment').forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.getAttribute('data-id');
                cancelAppointment(appointmentId);
            });
        });
    }
    
    // Función para cancelar una cita
    function cancelAppointment(appointmentId) {
        showConfirmationModal({
            title: 'Cancelar cita',
            message: '¿Estás seguro de que deseas cancelar esta cita?',
            confirmText: 'Sí, cancelar',
            cancelText: 'No, mantener',
            onConfirm: function() {
                fetch(`${SITE_URL}/api/appointments/cancel_appointment.php`, {
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
                    if (data.success) {
                        showSuccessNotification('Cita cancelada correctamente');
                        loadUserAppointments(); // Recargar la lista de citas
                        if (selectedDate) {
                            loadTimeSlots(selectedDate); // Recargar los horarios disponibles
                        }
                    } else {
                        showErrorNotification(data.message || 'Error al cancelar la cita');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorNotification('Error al cancelar la cita. Por favor, intenta de nuevo.');
                });
            }
        });
    }
    
    // Modificar la parte donde se verifica la sesión antes de mostrar el modal de reserva:
    function showReserveModal(slot) {
        // Verificar si el usuario está logueado (usando una variable global establecida desde PHP)
        if (!isUserLoggedIn) {
            // Si no está logueado, guardamos fecha y hora en localStorage
            localStorage.setItem('pending_appointment_date', selectedDate);
            localStorage.setItem('pending_appointment_time', slot.hora_inicio);
            
            // Redirigir a login con parámetros para redirección posterior
            window.location.href = `${SITE_URL}/pages/auth.php?action=login&require_login=true&redirect=appointments`;
            return;
        }
        
        // Continuar con el proceso de reserva...
    }
    
    // Cerrar el modal cuando se hace clic en la X o en el botón Cancelar
    document.querySelector('.close-modal').addEventListener('click', function() {
        reserveModal.style.display = 'none';
    });
    
    document.getElementById('cancel-appointment').addEventListener('click', function() {
        reserveModal.style.display = 'none';
    });
    
    // Cerrar el modal si se hace clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target === reserveModal) {
            reserveModal.style.display = 'none';
        }
    });
    
    // Función auxiliar para capitalizar texto
    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
});