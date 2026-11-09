document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Flatpickr para selección de días a bloquear
    flatpickr("#block-days", {
        mode: "multiple",
        dateFormat: "Y-m-d",
        locale: "es",
        minDate: "today",
        disable: [
            function(date) {
                // Deshabilitar sábados y domingos
                return (date.getDay() === 0 || date.getDay() === 6);
            }
        ]
    });
    
    // Inicializar Flatpickr para selección de una fecha específica
    const specificDatePicker = flatpickr("#specific-date", {
        mode: "single",
        dateFormat: "Y-m-d",
        locale: "es",
        minDate: "today",
        disable: [
            function(date) {
                // Deshabilitar sábados y domingos
                return (date.getDay() === 0 || date.getDay() === 6);
            }
        ],
        onChange: function(selectedDates, dateStr) {
            if(selectedDates.length > 0) {
                const selectedDate = selectedDates[0];
                // Guardar también el día de la semana (1-7, donde 1 es lunes)
                const dayOfWeek = selectedDate.getDay() === 0 ? 7 : selectedDate.getDay();
                document.getElementById('day-from-date').value = dayOfWeek;
                
                // Cargar horarios para esa fecha específica
                loadTimeSlotsForSpecificDate(dateStr, dayOfWeek);
            }
        }
    });
    
    // Esta es la función clave que controla la visibilidad del selector de motivo
    function checkBlockedSlots() {
        const blockedSlots = document.querySelectorAll('.time-slot-row.locked-slot');
        const blockReasonContainer = document.getElementById('block-reason-container');
        
        if (!blockReasonContainer) {
            console.error('No se encontró el contenedor de motivo de bloqueo');
            return;
        }
        
        console.log('Slots bloqueados:', blockedSlots.length); // Depuración
        
        if (blockedSlots.length > 0) {
            blockReasonContainer.style.display = 'block';
            
            // Hacer que el selector de motivo sea requerido cuando hay slots bloqueados
            const blockReasonSlot = document.getElementById('block-reason-slot');
            if (blockReasonSlot) {
                blockReasonSlot.required = true;
                
                // También hacer requerido el campo de otro motivo si está seleccionado "Otro"
                const otherReasonSlotInput = document.getElementById('other-reason-slot');
                if (otherReasonSlotInput && blockReasonSlot.value === 'Otro') {
                    otherReasonSlotInput.required = true;
                }
            }
        } else {
            blockReasonContainer.style.display = 'none';
            const otherReasonSlotGroup = document.getElementById('other-reason-slot-group');
            if (otherReasonSlotGroup) {
                otherReasonSlotGroup.style.display = 'none';
            }
            
            // No requerir el selector cuando no hay slots bloqueados
            const blockReasonSlot = document.getElementById('block-reason-slot');
            if (blockReasonSlot) {
                blockReasonSlot.required = false;
                blockReasonSlot.selectedIndex = 0; // Resetear la selección
                
                // También quitar el required del campo de otro motivo
                const otherReasonSlotInput = document.getElementById('other-reason-slot');
                if (otherReasonSlotInput) {
                    otherReasonSlotInput.required = false;
                }
            }
        }
    }

    // Añadir evento para manejar el selector de "Otro" motivo
    const blockReasonSlot = document.getElementById('block-reason-slot');
    const otherReasonSlotGroup = document.getElementById('other-reason-slot-group');

    if (blockReasonSlot && otherReasonSlotGroup) {
        blockReasonSlot.addEventListener('change', function() {
            if (this.value === 'Otro') {
                otherReasonSlotGroup.style.display = 'block';
            } else {
                otherReasonSlotGroup.style.display = 'none';
            }
        });
    }

    // Mostrar campo "otro motivo" si se selecciona "Otro"
    const blockReason = document.getElementById('block-reason');
    const otherReasonGroup = document.getElementById('other-reason-group');
    
    if (blockReason && otherReasonGroup) {
        blockReason.addEventListener('change', function() {
            if (this.value === 'Otro') {
                otherReasonGroup.style.display = 'block';
            } else {
                otherReasonGroup.style.display = 'none';
            }
        });
    }
    
    // Añadir y eliminar slots de tiempo
    const addTimeSlotBtn = document.getElementById('add-time-slot');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    
    // Modificar la parte de añadir slots de tiempo
    if (addTimeSlotBtn && timeSlotsContainer) {
        addTimeSlotBtn.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'time-slot-row';
            newRow.innerHTML = `
                <div class="form-group half">
                    <label>Hora inicio:</label>
                    <input type="time" name="hora_inicio[]" required>
                </div>
                <div class="form-group half">
                    <label>Hora fin:</label>
                    <input type="time" name="hora_fin[]" required>
                </div>
                <div class="slot-actions">
                    <button type="button" class="btn-lock-slot" title="Bloquear horario"><i class="fas fa-lock"></i></button>
                    <button type="button" class="btn-unlock-slot" title="Desbloquear horario" style="display:none;"><i class="fas fa-lock-open"></i></button>
                    <input type="hidden" name="slot_status[]" value="available">
                </div>
            `;
            timeSlotsContainer.appendChild(newRow);
            
            // Añadir eventos a los botones de bloquear/desbloquear
            const lockBtn = newRow.querySelector('.btn-lock-slot');
            const unlockBtn = newRow.querySelector('.btn-unlock-slot');
            const statusInput = newRow.querySelector('input[name="slot_status[]"]');
            
            lockBtn.addEventListener('click', function() {
                const slotRow = this.closest('.time-slot-row');
                slotRow.classList.add('locked-slot');
                statusInput.value = 'blocked';
                lockBtn.style.display = 'none';
                unlockBtn.style.display = 'block';
                
                // Forzar la visibilidad del selector de motivo
                const blockReasonContainer = document.getElementById('block-reason-container');
                if (blockReasonContainer) {
                    blockReasonContainer.style.display = 'block';
                    
                    // Hacer que el campo sea requerido
                    const blockReasonSlot = document.getElementById('block-reason-slot');
                    if (blockReasonSlot) {
                        blockReasonSlot.required = true;
                        // Hacer focus en el selector para llamar la atención del usuario
                        blockReasonSlot.focus();
                    }
                }
                
                // Verificar si hay slots bloqueados y mostrar selector de motivo
                checkBlockedSlots();
            });
            
            unlockBtn.addEventListener('click', function() {
                const slotRow = this.closest('.time-slot-row');
                slotRow.classList.remove('locked-slot');
                statusInput.value = 'available';
                unlockBtn.style.display = 'none';
                lockBtn.style.display = 'block';
                
                // Verificar si hay slots bloqueados y mostrar selector de motivo
                checkBlockedSlots();
            });
        });
    }
    
    // Cargar horarios existentes al seleccionar día
    const daySelect = document.getElementById('day-of-week');
    
    // Modificar la parte de cargar horarios existentes
    if (daySelect) {
        daySelect.addEventListener('change', function() {
            const selectedDay = this.value;
            
            // Limpiar horarios existentes
            timeSlotsContainer.innerHTML = '';
            
            // Cargar horarios del día seleccionado
            fetch(`${SITE_URL}/api/administrative-apis/get_day_time_slots.php?dia=${selectedDay}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.timeSlots.length > 0) {
                        data.timeSlots.forEach(slot => {
                            const newRow = document.createElement('div');
                            newRow.className = 'time-slot-row';
                            if (!slot.disponible) {
                                newRow.classList.add('locked-slot');
                            }
                            
                            newRow.innerHTML = `
                                <div class="form-group half">
                                    <label>Hora inicio:</label>
                                    <input type="time" name="hora_inicio[]" value="${slot.hora_inicio}" required>
                                </div>
                                <div class="form-group half">
                                    <label>Hora fin:</label>
                                    <input type="time" name="hora_fin[]" value="${slot.hora_fin}" required>
                                </div>
                                <div class="slot-actions">
                                    <button type="button" class="btn-lock-slot" title="Bloquear horario" ${!slot.disponible ? 'style="display:none;"' : ''}><i class="fas fa-lock"></i></button>
                                    <button type="button" class="btn-unlock-slot" title="Desbloquear horario" ${slot.disponible ? 'style="display:none;"' : ''}><i class="fas fa-lock-open"></i></button>
                                    <input type="hidden" name="slot_status[]" value="${slot.disponible ? 'available' : 'blocked'}">
                                </div>
                            `;
                            
                            timeSlotsContainer.appendChild(newRow);
                            
                            // Añadir eventos a los botones de bloquear/desbloquear
                            const lockBtn = newRow.querySelector('.btn-lock-slot');
                            const unlockBtn = newRow.querySelector('.btn-unlock-slot');
                            const statusInput = newRow.querySelector('input[name="slot_status[]"]');
                            
                            if (lockBtn && unlockBtn) {
                                lockBtn.addEventListener('click', function() {
                                    const slotRow = this.closest('.time-slot-row');
                                    slotRow.classList.add('locked-slot');
                                    statusInput.value = 'blocked';
                                    lockBtn.style.display = 'none';
                                    unlockBtn.style.display = 'block';
                                });
                                
                                unlockBtn.addEventListener('click', function() {
                                    const slotRow = this.closest('.time-slot-row');
                                    slotRow.classList.remove('locked-slot');
                                    statusInput.value = 'available';
                                    unlockBtn.style.display = 'none';
                                    lockBtn.style.display = 'block';
                                });
                            }
                        });
                    } else {
                        // Si no hay horarios, añadir uno vacío
                        addTimeSlotBtn.click();
                    }
                })
                .catch(error => {
                    console.error('Error al cargar horarios:', error);
                    // Si hay error, añadir uno vacío
                    addTimeSlotBtn.click();
                });
        });
        
        // Cargar los horarios del lunes por defecto
        daySelect.dispatchEvent(new Event('change'));
    }
    
    // Función para cargar horarios para una fecha específica - Corregida para evitar duplicados
    function loadTimeSlotsForSpecificDate(date, dayOfWeek) {
        const timeSlotsContainer = document.getElementById('time-slots-container');
        
        // Limpiar horarios existentes
        timeSlotsContainer.innerHTML = '';
        
        // Primero intentamos cargar horarios específicos para esa fecha
        fetch(`${SITE_URL}/api/administrative-apis/get_specific_date_time_slots.php?fecha=${date}`)
            .then(response => response.json())
            .then(data => {
                // Siempre limpiamos el contenedor antes de renderizar
                timeSlotsContainer.innerHTML = '';
                
                if (data.success && data.timeSlots.length > 0) {
                    // Si hay horarios específicos para esta fecha, los mostramos
                    renderTimeSlots(data.timeSlots);
                } else {
                    // Si no hay horarios específicos, cargamos los horarios por defecto según el día de la semana
                    fetch(`${SITE_URL}/api/administrative-apis/get_day_time_slots.php?dia=${dayOfWeek}`)
                        .then(response => response.json())
                        .then(data => {
                            // Limpiamos de nuevo por si acaso
                            timeSlotsContainer.innerHTML = '';
                            
                            if (data.success && data.timeSlots.length > 0) {
                                renderTimeSlots(data.timeSlots);
                            } else {
                                // Si tampoco hay horarios por defecto, creamos slots predeterminados
                                createDefaultTimeSlots();
                            }
                        })
                        .catch(error => {
                            console.error('Error al cargar horarios del día:', error);
                            timeSlotsContainer.innerHTML = '';
                            createDefaultTimeSlots();
                        });
                }
            })
            .catch(error => {
                console.error('Error al cargar horarios para fecha específica:', error);
                timeSlotsContainer.innerHTML = '';
                
                // Si hay error, intentamos cargar los horarios por defecto según el día de la semana
                fetch(`${SITE_URL}/api/administrative-apis/get_day_time_slots.php?dia=${dayOfWeek}`)
                    .then(response => response.json())
                    .then(data => {
                        timeSlotsContainer.innerHTML = '';
                        if (data.success && data.timeSlots.length > 0) {
                            renderTimeSlots(data.timeSlots);
                        } else {
                            createDefaultTimeSlots();
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar horarios del día:', error);
                        timeSlotsContainer.innerHTML = '';
                        createDefaultTimeSlots();
                    });
            });
    }
    
    // Función para crear slots de tiempo predeterminados
    function createDefaultTimeSlots() {
        const timeSlotsContainer = document.getElementById('time-slots-container');
        const defaultSlots = [
            { hora_inicio: '09:00', hora_fin: '10:00', disponible: true },
            { hora_inicio: '10:00', hora_fin: '11:00', disponible: true },
            { hora_inicio: '11:00', hora_fin: '12:00', disponible: true },
            { hora_inicio: '12:00', hora_fin: '13:00', disponible: true },
            { hora_inicio: '16:00', hora_fin: '17:00', disponible: true },
            { hora_inicio: '17:00', hora_fin: '18:00', disponible: true },
            { hora_inicio: '18:00', hora_fin: '19:00', disponible: true },
            { hora_inicio: '19:00', hora_fin: '20:00', disponible: true }
        ];
        
        renderTimeSlots(defaultSlots);
    }
    
    // Función para renderizar los slots de tiempo
    function renderTimeSlots(slots) {
        const timeSlotsContainer = document.getElementById('time-slots-container');
        
        slots.forEach(slot => {
            const newRow = document.createElement('div');
            newRow.className = 'time-slot-row';
            
            if (!slot.disponible) {
                newRow.classList.add('locked-slot');
            }
            
            newRow.innerHTML = `
                <div class="form-group half">
                    <label>Hora inicio:</label>
                    <input type="time" name="hora_inicio[]" value="${slot.hora_inicio}" required>
                </div>
                <div class="form-group half">
                    <label>Hora fin:</label>
                    <input type="time" name="hora_fin[]" value="${slot.hora_fin}" required>
                </div>
                <div class="slot-actions">
                    <button type="button" class="btn-lock-slot" title="Bloquear horario" ${!slot.disponible ? 'style="display:none;"' : ''}><i class="fas fa-lock"></i></button>
                    <button type="button" class="btn-unlock-slot" title="Desbloquear horario" ${slot.disponible ? 'style="display:none;"' : ''}><i class="fas fa-lock-open"></i></button>
                    <input type="hidden" name="slot_status[]" value="${slot.disponible ? 'available' : 'blocked'}">
                </div>
            `;
            
            timeSlotsContainer.appendChild(newRow);
            
            // Añadir eventos a los botones de bloquear/desbloquear
            const lockBtn = newRow.querySelector('.btn-lock-slot');
            const unlockBtn = newRow.querySelector('.btn-unlock-slot');
            const statusInput = newRow.querySelector('input[name="slot_status[]"]');
            
            if (lockBtn && unlockBtn) {
                lockBtn.addEventListener('click', function() {
                    const slotRow = this.closest('.time-slot-row');
                    slotRow.classList.add('locked-slot');
                    statusInput.value = 'blocked';
                    lockBtn.style.display = 'none';
                    unlockBtn.style.display = 'block';
                    
                    // Forzar la visibilidad del selector de motivo
                    const blockReasonContainer = document.getElementById('block-reason-container');
                    if (blockReasonContainer) {
                        blockReasonContainer.style.display = 'block';
                        
                        // Hacer que el campo sea requerido
                        const blockReasonSlot = document.getElementById('block-reason-slot');
                        if (blockReasonSlot) {
                            blockReasonSlot.required = true;
                            // Hacer focus en el selector para llamar la atención del usuario
                            blockReasonSlot.focus();
                        }
                    }
                    
                    // Verificar si hay slots bloqueados y mostrar selector de motivo
                    checkBlockedSlots();
                });
                
                unlockBtn.addEventListener('click', function() {
                    const slotRow = this.closest('.time-slot-row');
                    slotRow.classList.remove('locked-slot');
                    statusInput.value = 'available';
                    unlockBtn.style.display = 'none';
                    lockBtn.style.display = 'block';
                });
            }
        });
        
        // Verificar slots bloqueados al cargar
        setTimeout(() => {
            checkBlockedSlots();
            checkTimeSlotStatus(); // Añadir esta nueva verificación
            
            // Forzar visibilidad del selector si hay slots bloqueados
            const lockedSlots = document.querySelectorAll('.time-slot-row.locked-slot');
            if (lockedSlots.length > 0) {
                const blockReasonContainer = document.getElementById('block-reason-container');
                if (blockReasonContainer) {
                    blockReasonContainer.style.display = 'block';
                }
            }
        }, 300);
    }
    
    // Comprobar el estado de los horarios después de renderizar
    function checkTimeSlotStatus() {
        const lockedSlots = document.querySelectorAll('.time-slot-row.locked-slot');
        console.log('Comprobando slots bloqueados:', lockedSlots.length);
        
        // Si hay al menos un slot bloqueado, mostrar el selector de motivo
        if (lockedSlots.length > 0) {
            const reasonContainer = document.getElementById('block-reason-container');
            if (reasonContainer) {
                reasonContainer.style.display = 'block';
            }
        }
    }
    
    // Modificar la sección de gestionar botones de desbloqueo de días
    const unblockButtons = document.querySelectorAll('.btn-unblock-day');
    unblockButtons.forEach(button => {
        button.addEventListener('click', function() {
            const dayId = this.getAttribute('data-id');
            const dayDate = this.closest('tr').querySelector('td:first-child').textContent;
            
            // Crear modal de confirmación en lugar de alert
            const modal = document.createElement('div');
            modal.className = 'user-data-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Confirmar desbloqueo</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas desbloquear el día <strong>${dayDate}</strong>?</p>
                        <p>Este día volverá a estar disponible para citas.</p>
                        <div class="confirmation-buttons">
                            <button class="btn btn-secondary" id="cancel-unblock">Cancelar</button>
                            <button class="btn btn-primary" id="confirm-unblock">Confirmar</button>
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
            
            // Manejar botón de cancelar
            const cancelBtn = modal.querySelector('#cancel-unblock');
            cancelBtn.addEventListener('click', function() {
                modal.remove();
            });
            
            // Manejar botón confirmar
            const confirmBtn = modal.querySelector('#confirm-unblock');
            confirmBtn.addEventListener('click', function() {
                // Mostrar indicador de carga
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                confirmBtn.disabled = true;
                
                fetch(`${SITE_URL}/api/administrative-apis/unblock_day.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: dayId })
                })
                .then(response => response.json())
                .then(data => {
                    modal.remove();
                    
                    if (data.success) {
                        // Crear modal de éxito
                        const successModal = document.createElement('div');
                        successModal.className = 'user-data-modal';
                        successModal.innerHTML = `
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3>Éxito</h3>
                                    <span class="close-modal">&times;</span>
                                </div>
                                <div class="modal-body">
                                    <p>El día se ha desbloqueado correctamente.</p>
                                    <div class="confirmation-buttons">
                                        <button class="btn btn-primary" id="ok-button">Aceptar</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(successModal);
                        
                        // Manejar cierre del modal de éxito
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
                        // Mostrar modal de error
                        const errorModal = document.createElement('div');
                        errorModal.className = 'user-data-modal';
                        errorModal.innerHTML = `
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3>Error</h3>
                                    <span class="close-modal">&times;</span>
                                </div>
                                <div class="modal-body">
                                    <p>${data.message || 'Error al desbloquear el día'}</p>
                                    <div class="confirmation-buttons">
                                        <button class="btn btn-danger" id="error-ok">Entendido</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(errorModal);
                        
                        // Manejar cierre del modal de error
                        const closeErrorBtn = errorModal.querySelector('.close-modal');
                        closeErrorBtn.addEventListener('click', function() {
                            errorModal.remove();
                        });
                        
                        const errorOkBtn = errorModal.querySelector('#error-ok');
                        errorOkBtn.addEventListener('click', function() {
                            errorModal.remove();
                        });
                    }
                })
                .catch(error => {
                    modal.remove();
                    console.error('Error:', error);
                    
                    // Mostrar modal de error
                    const errorModal = document.createElement('div');
                    errorModal.className = 'user-data-modal';
                    errorModal.innerHTML = `
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Error</h3>
                                <span class="close-modal">&times;</span>
                            </div>
                            <div class="modal-body">
                                <p>Error al procesar la solicitud. Por favor, intenta de nuevo.</p>
                                <div class="confirmation-buttons">
                                    <button class="btn btn-danger" id="error-ok">Entendido</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(errorModal);
                    
                    // Manejar cierre del modal de error
                    const closeErrorBtn = errorModal.querySelector('.close-modal');
                    closeErrorBtn.addEventListener('click', function() {
                        errorModal.remove();
                    });
                    
                    const errorOkBtn = errorModal.querySelector('#error-ok');
                    errorOkBtn.addEventListener('click', function() {
                        errorModal.remove();
                    });
                });
            });
        });
    });
    
    // Manejar los botones para desbloquear todos los horarios de un día
    const unblockDayHoursButtons = document.querySelectorAll('.btn-unblock-day-hours');
    unblockDayHoursButtons.forEach(button => {
        button.addEventListener('click', function() {
            const fecha = this.getAttribute('data-fecha');
            const fechaFormateada = this.closest('tr').querySelector('td:first-child').textContent;
            
            // Crear modal de confirmación
            const modal = document.createElement('div');
            modal.className = 'user-data-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Confirmar desbloqueo de horarios</h3>
                        <span class="close-modal">&times;</span>
                    </div>
                    <div class="modal-body">
                        <p>¿Estás seguro de que deseas desbloquear todos los horarios para el día <strong>${fechaFormateada}</strong>?</p>
                        <p>Todos los horarios bloqueados volverán a estar disponibles para citas.</p>
                        <div class="confirmation-buttons">
                            <button class="btn btn-secondary" id="cancel-action">Cancelar</button>
                            <button class="btn btn-primary" id="confirm-action">Desbloquear todos</button>
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
                // Cambiar el botón a estado de carga
                confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                confirmBtn.disabled = true;
                
                fetch(`${SITE_URL}/api/administrative-apis/unblock_day_hours.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ fecha: fecha })
                })
                .then(response => response.json())
                .then(data => {
                    modal.remove();
                    
                    if (data.success) {
                        // Crear modal de éxito
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
                                        <i class="fas fa-check-circle success-icon" style="font-size: 3rem; color: #28a745; margin-bottom: 15px;"></i>
                                        <p>${data.message}</p>
                                    </div>
                                    <div class="confirmation-buttons">
                                        <button class="btn btn-primary" id="ok-button">Aceptar</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(successModal);
                        
                        // Manejar cierre del modal de éxito
                        const closeSuccessBtn = successModal.querySelector('.close-modal');
                        closeSuccessBtn.addEventListener('click', function() {
                            successModal.remove();
                            location.reload(); // Recargar para ver los cambios
                        });
                        
                        const okBtn = successModal.querySelector('#ok-button');
                        okBtn.addEventListener('click', function() {
                            successModal.remove();
                            location.reload(); // Recargar para ver los cambios
                        });
                    } else {
                        // Reemplazar alert por modal personalizado
                        const errorModal = document.createElement('div');
                        errorModal.className = 'user-data-modal';
                        errorModal.innerHTML = `
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h3>Error</h3>
                                    <span class="close-modal">&times;</span>
                                </div>
                                <div class="modal-body">
                                    <div class="error-message-container">
                                        <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #dc3545; margin-bottom: 15px;"></i>
                                        <p>${data.message || 'Error al desbloquear horarios'}</p>
                                    </div>
                                    <div class="confirmation-buttons">
                                        <button class="btn btn-primary" id="error-ok">Entendido</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        document.body.appendChild(errorModal);
                        
                        // Manejar cierre del modal de error
                        const closeErrorBtn = errorModal.querySelector('.close-modal');
                        closeErrorBtn.addEventListener('click', function() {
                            errorModal.remove();
                        });
                        
                        const errorOkBtn = errorModal.querySelector('#error-ok');
                        errorOkBtn.addEventListener('click', function() {
                            errorModal.remove();
                        });
                    }
                })
                .catch(error => {
                    modal.remove();
                    console.error('Error:', error);
                    
                    // Reemplazar alert por modal personalizado
                    const errorModal = document.createElement('div');
                    errorModal.className = 'user-data-modal';
                    errorModal.innerHTML = `
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Error</h3>
                                <span class="close-modal">&times;</span>
                            </div>
                            <div class="modal-body">
                                <div class="error-message-container">
                                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #dc3545; margin-bottom: 15px;"></i>
                                    <p>Error al desbloquear horarios. Por favor, intenta de nuevo.</p>
                                    <p class="technical-error">Detalles: ${error.message}</p>
                                </div>
                                <div class="confirmation-buttons">
                                    <button class="btn btn-primary" id="error-ok">Entendido</button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(errorModal);
                    
                    // Manejar cierre del modal de error
                    const closeErrorBtn = errorModal.querySelector('.close-modal');
                    closeErrorBtn.addEventListener('click', function() {
                        errorModal.remove();
                    });
                    
                    const errorOkBtn = errorModal.querySelector('#error-ok');
                    errorOkBtn.addEventListener('click', function() {
                        errorModal.remove();
                    });
                });
            });
        });
    });
    
    // Llamar a checkBlockedSlots inmediatamente después de que se cargue la página
    setTimeout(checkBlockedSlots, 500);
    
    // Verificación adicional para mostrar el selector de motivo al cargar la página
    setTimeout(function() {
        const lockedSlots = document.querySelectorAll('.time-slot-row.locked-slot');
        if (lockedSlots.length > 0) {
            const blockReasonContainer = document.getElementById('block-reason-container');
            if (blockReasonContainer) {
                blockReasonContainer.style.display = 'block';
                console.log('Selector de motivo mostrado por inicialización');
            }
        }
    }, 800);
    
    // Unificar la función que maneja los eventos de cambio del selector de motivo
    function setupReasonChangeListeners() {
        // Para bloqueo de horarios específicos
        const blockReasonSlot = document.getElementById('block-reason-slot');
        const otherReasonSlotGroup = document.getElementById('other-reason-slot-group');
        const otherReasonSlotInput = document.getElementById('other-reason-slot');
        
        if (blockReasonSlot && otherReasonSlotGroup && otherReasonSlotInput) {
            blockReasonSlot.addEventListener('change', function() {
                if (this.value === 'Otro') {
                    otherReasonSlotGroup.style.display = 'block';
                    otherReasonSlotInput.required = true;
                } else {
                    otherReasonSlotGroup.style.display = 'none';
                    otherReasonSlotInput.required = false;
                }
            });
            
            // Validación para permitir solo letras en el campo de motivo para horarios
            otherReasonSlotInput.addEventListener('input', function(e) {
                // Solo permitir letras y espacios
                this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
            });
        }
        
        // Para bloqueo de días completos
        const blockReason = document.getElementById('block-reason');
        const otherReasonGroup = document.getElementById('other-reason-group');
        const otherReasonInput = document.getElementById('other-reason');
        
        if (blockReason && otherReasonGroup && otherReasonInput) {
            blockReason.addEventListener('change', function() {
                if (this.value === 'Otro') {
                    otherReasonGroup.style.display = 'block';
                    otherReasonInput.required = true;
                } else {
                    otherReasonGroup.style.display = 'none';
                    otherReasonInput.required = false;
                }
            });
            
            // Validación para permitir solo letras en el campo de motivo para días
            otherReasonInput.addEventListener('input', function(e) {
                // Solo permitir letras y espacios
                this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
            });
        }
    }
    
    // Llamar a la función para configurar los listeners
    setupReasonChangeListeners();
    
    // Validación adicional antes de enviar el formulario de horarios
    const timeSlotsForm = document.getElementById('time-slots-form');
    if (timeSlotsForm) {
        timeSlotsForm.addEventListener('submit', function(event) {
            const blockedSlots = document.querySelectorAll('.time-slot-row.locked-slot');
            if (blockedSlots.length > 0) {
                const blockReasonSlot = document.getElementById('block-reason-slot');
                if (!blockReasonSlot || !blockReasonSlot.value) {
                    event.preventDefault();
                    alert('Debe seleccionar un motivo para bloquear horarios.');
                    return false;
                }
                
                if (blockReasonSlot.value === 'Otro') {
                    const otherReasonSlot = document.getElementById('other-reason-slot');
                    if (!otherReasonSlot || !otherReasonSlot.value.trim()) {
                        event.preventDefault();
                        alert('Debe especificar un motivo personalizado para bloquear horarios.');
                        return false;
                    }
                }
            }
        });
    }

    // Validación adicional antes de enviar el formulario de días completos
    const blockDaysForm = document.getElementById('block-days-form');
    if (blockDaysForm) {
        blockDaysForm.addEventListener('submit', function(event) {
            const blockReason = document.getElementById('block-reason');
            if (!blockReason || !blockReason.value) {
                event.preventDefault();
                alert('Debe seleccionar un motivo para bloquear días.');
                return false;
            }
            
            if (blockReason.value === 'Otro') {
                const otherReason = document.getElementById('other-reason');
                if (!otherReason || !otherReason.value.trim()) {
                    event.preventDefault();
                    alert('Debe especificar un motivo personalizado para bloquear días.');
                    return false;
                }
            }
        });
    }
});