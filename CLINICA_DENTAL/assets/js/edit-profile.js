document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los campos del formulario
    const editNombre = document.getElementById('edit-nombre');
    const editEmail = document.getElementById('edit-email');
    const editPassword = document.getElementById('edit-password');
    const editPassword2 = document.getElementById('edit-password2');
    const editDireccion = document.getElementById('edit-direccion');
    const editTelefono = document.getElementById('edit-telefono');
    const editForm = document.getElementById('edit-profile-form');
    
    // Estado de validación de los campos
    const validFields = {
        nombre: true, // Inicialmente válido porque viene con datos del usuario
        email: true,  // Inicialmente válido porque viene con datos del usuario
        password: true, // Opcional si está vacío
        password2: true, // Opcional si está vacío
        direccion: true, // Opcional
        telefono: true   // Opcional
    };
    
    // Funciones de validación
    function validateNombre(nombre) {
        // Solo letras sin acentos, números, guiones y guiones bajos, máximo 16 caracteres
        const regex = /^[a-zA-Z0-9_-]{1,16}$/;
        return regex.test(nombre);
    }
    
    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
    
    function validatePassword(password) {
        // Al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/;
        
        if (!regex.test(password)) {
            // Verificar cada requisito para dar feedback específico
            let errorMsg = 'La contraseña debe cumplir los siguientes requisitos: ';
            if (password.length < 8) errorMsg += 'Al menos 8 caracteres* ';
            if (!/[A-Z]/.test(password)) errorMsg += 'Al menos una letra mayúscula* ';
            if (!/[a-z]/.test(password)) errorMsg += 'Al menos una letra minúscula* ';
            if (!/\d/.test(password)) errorMsg += 'Al menos un número* ';
            if (!/[^\da-zA-Z]/.test(password)) errorMsg += 'Al menos un carácter especial* ';
            
            return { valid: false, message: errorMsg };
        }
        
        return { valid: true };
    }
    
    function validateTelefono(telefono) {
        if (!telefono) return true; // Es opcional
        const regex = /^6[0-9]{8}$/;
        return regex.test(telefono);
    }
    
    function isNumberKey(evt) {
        const charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
    
    // Hacer global isNumberKey
    window.isNumberKey = isNumberKey;
    
    // Función para verificar caracteres no permitidos
    function hasInvalidChars(value) {
        return /[áàäâéèëêíìïîóòöôúùüûñÁÀÄÂÉÈËÊÍÌÏÎÓÒÖÔÚÙÜÛÑ]/.test(value);
    }

    // Funciones para mostrar/ocultar mensajes de error
    function showError(elementId, message) {
        const errorElement = document.getElementById(`${elementId}-error`);
        if (errorElement) {
            errorElement.innerHTML = message;
            errorElement.style.display = 'block';
        }
        
        const inputElement = document.getElementById(elementId);
        if (inputElement) {
            inputElement.classList.add('error-field');
            inputElement.classList.remove('success-field');
        }
    }
    
    function clearError(elementId) {
        const errorElement = document.getElementById(`${elementId}-error`);
        if (errorElement) {
            errorElement.innerHTML = '';
            errorElement.style.display = 'none';
        }
    }
    
    function markValid(elementId) {
        clearError(elementId);
        
        const inputElement = document.getElementById(elementId);
        if (inputElement) {
            inputElement.classList.remove('error-field');
            inputElement.classList.add('success-field');
        }
    }
    
    function clearAllErrors() {
        const errorElements = document.querySelectorAll('.field-error');
        const inputElements = document.querySelectorAll('.form-group input');
        
        errorElements.forEach(element => {
            element.innerHTML = '';
            element.style.display = 'none';
        });
        
        inputElements.forEach(element => {
            element.classList.remove('error-field');
            element.classList.remove('success-field');
        });
    }
    
    // Validación de coincidencia de contraseñas
    function validatePasswordMatch() {
        if (!editPassword || !editPassword2) return true;
        
        const password = editPassword.value;
        const password2 = editPassword2.value;
        
        // Si ambas están vacías, no hay problema (no se cambia la contraseña)
        if (password === '' && password2 === '') {
            clearError('edit-password2');
            validFields.password = true;
            validFields.password2 = true;
            return true;
        }
        
        // Si una está vacía pero la otra no
        if ((password === '' && password2 !== '') || (password !== '' && password2 === '')) {
            showError('edit-password2', 'Ambos campos de contraseña deben estar llenos');
            validFields.password2 = false;
            return false;
        }
        
        // Si no coinciden
        if (password !== password2) {
            showError('edit-password2', 'Las contraseñas no coinciden');
            validFields.password2 = false;
            return false;
        }
        
        // Si coinciden
        markValid('edit-password2');
        validFields.password2 = true;
        return true;
    }
    
    // Validación en tiempo real para el nombre
    if (editNombre) {
        // Validar caracteres no permitidos mientras se escribe
        editNombre.addEventListener('input', function() {
            const nombre = this.value.trim();
            
            // Verificar caracteres no permitidos en tiempo real
            if (hasInvalidChars(nombre)) {
                const invalidChars = nombre.match(/[áàäâéèëêíìïîóòöôúùüûñÁÀÄÂÉÈËÊÍÌÏÎÓÒÖÔÚÙÜÛÑ]/g);
                showError(this.id, `Caracteres no permitidos: ${invalidChars.join(', ')}. No se permiten acentos, diéresis o ñ.`);
                validFields.nombre = false;
            } else {
                clearError(this.id);
            }
        });
        
        // Validación completa al perder el foco
        editNombre.addEventListener('blur', function() {
            const nombre = this.value.trim();
            
            if (nombre === '') {
                showError(this.id, 'El nombre es obligatorio');
                validFields.nombre = false;
            } else if (hasInvalidChars(nombre)) {
                const invalidChars = nombre.match(/[áàäâéèëêíìïîóòöôúùüûñÁÀÄÂÉÈËÊÍÌÏÎÓÒÖÔÚÙÜÛÑ]/g);
                showError(this.id, `Caracteres no permitidos: ${invalidChars.join(', ')}. No se permiten acentos, diéresis o ñ.`);
                validFields.nombre = false;
            } else if (!validateNombre(nombre)) {
                showError(this.id, 'El nombre debe tener máximo 16 caracteres y solo puede contener letras (sin acentos), números, guiones y guiones bajos.');
                validFields.nombre = false;
            } else {
                markValid(this.id);
                validFields.nombre = true;
            }
        });
    }
    
    // Validación en tiempo real para el email
    if (editEmail) {
        editEmail.addEventListener('blur', function() {
            const email = this.value.trim();
            
            if (email === '') {
                showError(this.id, 'El email es obligatorio');
                validFields.email = false;
            } else if (!validateEmail(email)) {
                showError(this.id, 'Por favor, introduce un email válido');
                validFields.email = false;
            } else {
                markValid(this.id);
                validFields.email = true;
            }
        });
    }
    
    // Validación en tiempo real para la contraseña
    if (editPassword) {
        editPassword.addEventListener('blur', function() {
            const password = this.value;
            
            // Si la contraseña está vacía, es válido (no se cambia)
            if (password === '') {
                clearError(this.id);
                validFields.password = true;
                
                // Si el otro campo tiene contenido, validar la coincidencia
                if (editPassword2 && editPassword2.value !== '') {
                    validatePasswordMatch();
                }
                return;
            }
            
            // Si hay contraseña, validar requisitos
            const validation = validatePassword(password);
            if (!validation.valid) {
                showError(this.id, validation.message);
                validFields.password = false;
            } else {
                markValid(this.id);
                validFields.password = true;
                
                // Si el campo de confirmación ya tiene contenido, verificar coincidencia
                if (editPassword2 && editPassword2.value !== '') {
                    validatePasswordMatch();
                }
            }
        });
        
        // Revalidar match en tiempo real si se modifica
        editPassword.addEventListener('input', function() {
            if (editPassword2 && editPassword2.value !== '') {
                validatePasswordMatch();
            }
        });
    }
    
    // Validación en tiempo real para la confirmación de contraseña
    if (editPassword2) {
        // Validar en tiempo real mientras se escribe
        editPassword2.addEventListener('input', validatePasswordMatch);
        
        // Validar al perder el foco
        editPassword2.addEventListener('blur', function() {
            if (editPassword && (editPassword.value !== '' || this.value !== '')) {
                validatePasswordMatch();
            }
        });
    }
    
    // Validación en tiempo real para el teléfono
    if (editTelefono) {
        // Limitar a solo números
        editTelefono.addEventListener('keypress', function(e) {
            const key = e.key;
            if (!/^\d$/.test(key)) {
                e.preventDefault();
            }
        });
        
        editTelefono.addEventListener('blur', function() {
            const telefono = this.value.trim();
            
            // Solo validar si hay contenido (campo opcional)
            if (telefono !== '') {
                if (!validateTelefono(telefono)) {
                    showError(this.id, 'Introduce un número de teléfono móvil válido (9 dígitos que comience por 6)');
                    validFields.telefono = false;
                } else {
                    markValid(this.id);
                    validFields.telefono = true;
                }
            } else {
                clearError(this.id);
                validFields.telefono = true;
            }
        });
    }
    
    // Validación al enviar el formulario
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Siempre prevenir el envío por defecto
            
            // Forzar validación de todos los campos
            if (editNombre) editNombre.dispatchEvent(new Event('blur'));
            if (editEmail) editEmail.dispatchEvent(new Event('blur'));
            if (editPassword) editPassword.dispatchEvent(new Event('blur'));
            if (editPassword2) editPassword2.dispatchEvent(new Event('blur'));
            if (editTelefono && editTelefono.value.trim() !== '') {
                editTelefono.dispatchEvent(new Event('blur'));
            }
            
            // Verificar que todos los campos sean válidos
            const formValid = validFields.nombre && validFields.email && 
                             validFields.password && validFields.password2 && 
                             validFields.telefono;
            
            if (formValid) {
                console.log('Formulario válido, enviando...');
                this.submit();
            } else {
                console.log('Formulario con errores, no se envía', validFields);
                
                // Crear un resumen de errores en la parte superior
                let errorSummary = document.querySelector('.error-summary');
                if (!errorSummary) {
                    errorSummary = document.createElement('div');
                    errorSummary.className = 'error-summary';
                    errorSummary.innerHTML = '<h4>Por favor corrige los siguientes errores:</h4><ul></ul>';
                    const formElement = this;
                    formElement.parentNode.insertBefore(errorSummary, formElement);
                }
                
                const errorList = errorSummary.querySelector('ul');
                errorList.innerHTML = '';
                
                // Añadir cada error al resumen
                document.querySelectorAll('.field-error').forEach(errorElement => {
                    if (errorElement.innerHTML) {
                        const errorText = errorElement.innerHTML.replace(/<br>-/g, ' -');
                        const errorItem = document.createElement('li');
                        errorItem.textContent = errorText;
                        errorList.appendChild(errorItem);
                    }
                });
                
                // Enfocar el primer campo con error
                if (!validFields.nombre) editNombre.focus();
                else if (!validFields.email) editEmail.focus();
                else if (!validFields.password) editPassword.focus();
                else if (!validFields.password2) editPassword2.focus();
                else if (!validFields.telefono) editTelefono.focus();
                
                // Hacer scroll al resumen de errores
                errorSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
});