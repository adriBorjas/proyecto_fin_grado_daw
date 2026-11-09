document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los elementos del formulario
    const registerForm = document.getElementById('register-form');
    const regNombre = document.getElementById('reg-nombre');
    const regEmail = document.getElementById('reg-email');
    const regPassword = document.getElementById('reg-password');
    const regPassword2 = document.getElementById('reg-password2');
    const regTelefono = document.getElementById('reg-telefono');
    
    // Banderas para seguir estado de validación
    const validFields = {
        nombre: false,
        email: false,
        password: false,
        password2: false,
        telefono: true // Opcional, por defecto true
    };
    
    // FUNCIONES DE VALIDACIÓN
    
    // Validar nombre: solo letras sin acentos, números, guiones y guiones bajos, máximo 16 caracteres
    function validateNombre(nombre) {
        return /^[a-zA-Z0-9_-]{1,16}$/.test(nombre);
    }
    
    // Validar email
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // Validar contraseña: mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial
    function validatePassword(password) {
        const minLength = password.length >= 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumber = /\d/.test(password);
        const hasSpecialChar = /[^\w\s]/.test(password);
        
        const isValid = minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
        
        // Construir mensaje detallado si hay error
        if (!isValid) {
            let errorMsg = 'La contraseña debe cumplir los siguientes requisitos:';
            if (!minLength) errorMsg += '<br>- Al menos 8 caracteres';
            if (!hasUpperCase) errorMsg += '<br>- Al menos una letra mayúscula';
            if (!hasLowerCase) errorMsg += '<br>- Al menos una letra minúscula';
            if (!hasNumber) errorMsg += '<br>- Al menos un número';
            if (!hasSpecialChar) errorMsg += '<br>- Al menos un carácter especial';
            
            return { valid: false, message: errorMsg };
        }
        
        return { valid: true };
    }
    
    // Validar teléfono (opcional, pero si existe debe ser móvil español)
    function validateTelefono(telefono) {
        if (!telefono) return true; // Si está vacío, es válido (opcional)
        return /^6[0-9]{8}$/.test(telefono);
    }
    
    // Validar caracteres no permitidos (acentos, ñ)
    function hasInvalidChars(value) {
        return /[áàäâéèëêíìïîóòöôúùüûñÁÀÄÂÉÈËÊÍÌÏÎÓÒÖÔÚÙÜÛÑ]/.test(value);
    }
    
    // FUNCIONES PARA MOSTRAR ERRORES
    
    // Mostrar error en un campo
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
            // No limpiar el valor
        }
    }
    
    // Limpiar error de un campo
    function clearError(elementId) {
        const errorElement = document.getElementById(`${elementId}-error`);
        if (errorElement) {
            errorElement.innerHTML = '';
            errorElement.style.display = 'none';
        }
    }
    
    // Marcar campo como válido
    function markValid(elementId) {
        clearError(elementId);
        
        const inputElement = document.getElementById(elementId);
        if (inputElement) {
            inputElement.classList.remove('error-field');
            inputElement.classList.add('success-field');
            // No limpiar el valor
        }
    }
    
    // VALIDACIONES EN TIEMPO REAL
    
    // Validación del nombre de usuario
    if (regNombre) {
        // Validar mientras se escribe (para caracteres no permitidos)
        regNombre.addEventListener('input', function() {
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
        regNombre.addEventListener('blur', function() {
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
    
    // Validación del correo electrónico
    if (regEmail) {
        regEmail.addEventListener('blur', function() {
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
    
    // Validación de la contraseña
    if (regPassword) {
        regPassword.addEventListener('blur', function() {
            const password = this.value;
            
            if (password === '') {
                showError(this.id, 'La contraseña es obligatoria');
                validFields.password = false;
            } else {
                const validation = validatePassword(password);
                if (!validation.valid) {
                    showError(this.id, validation.message);
                    validFields.password = false;
                } else {
                    markValid(this.id);
                    validFields.password = true;
                    
                    // Revalidar confirmación si ya hay contenido
                    if (regPassword2 && regPassword2.value !== '') {
                        validatePasswordMatch();
                    }
                }
            }
        });
        
        // Revalidar match en tiempo real si se modifica
        regPassword.addEventListener('input', function() {
            if (regPassword2 && regPassword2.value !== '') {
                validatePasswordMatch();
            }
        });
    }
    
    // Función para validar coincidencia de contraseñas
    function validatePasswordMatch() {
        if (!regPassword || !regPassword2) return;
        
        const password = regPassword.value;
        const password2 = regPassword2.value;
        
        if (password2 === '') {
            showError('reg-password2', 'Por favor, confirma tu contraseña');
            validFields.password2 = false;
            return;
        }
        
        if (password !== password2) {
            showError('reg-password2', 'Las contraseñas no coinciden');
            validFields.password2 = false;
        } else {
            markValid('reg-password2');
            validFields.password2 = true;
        }
    }
    
    // Validación de la confirmación de contraseña
    if (regPassword2) {
        regPassword2.addEventListener('input', validatePasswordMatch);
        regPassword2.addEventListener('blur', validatePasswordMatch);
    }
    
    // Validación del teléfono
    if (regTelefono) {
        // Limitar a solo números
        regTelefono.addEventListener('keypress', function(e) {
            const key = e.key;
            if (!/^\d$/.test(key)) {
                e.preventDefault();
            }
        });
        
        regTelefono.addEventListener('blur', function() {
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
    
    // VALIDACIÓN AL ENVIAR EL FORMULARIO
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Siempre prevenir el envío por defecto
            
            // Forzar validación de todos los campos
            if (regNombre) regNombre.dispatchEvent(new Event('blur'));
            if (regEmail) regEmail.dispatchEvent(new Event('blur'));
            if (regPassword) regPassword.dispatchEvent(new Event('blur'));
            if (regPassword2) regPassword2.dispatchEvent(new Event('blur'));
            if (regTelefono && regTelefono.value.trim() !== '') {
                regTelefono.dispatchEvent(new Event('blur'));
            }
            
            // Verificar que todos los campos obligatorios sean válidos
            const formValid = validFields.nombre && validFields.email && 
                             validFields.password && validFields.password2 && 
                             validFields.telefono;
            
            if (formValid) {
                console.log('Formulario válido, enviando...');
                this.submit();
            } else {
                console.log('Formulario con errores, no se envía', validFields);
                
                // Crear un resumen de errores en la parte superior si no existe
                let errorSummary = document.querySelector('.error-summary');
                if (!errorSummary) {
                    errorSummary = document.createElement('div');
                    errorSummary.className = 'error-summary';
                    errorSummary.innerHTML = '<h4>Por favor corrige los siguientes errores:</h4><ul></ul>';
                    // Insertar el resumen antes del formulario
                    const formContainer = document.getElementById('register-form-container');
                    const formElement = document.getElementById('register-form');
                    if (formContainer && formElement) {
                        formContainer.insertBefore(errorSummary, formElement);
                    }
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
                if (!validFields.nombre) regNombre.focus();
                else if (!validFields.email) regEmail.focus();
                else if (!validFields.password) regPassword.focus();
                else if (!validFields.password2) regPassword2.focus();
                else if (!validFields.telefono) regTelefono.focus();
                
                // Hacer scroll al resumen de errores
                errorSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
    
    // Modificar las funciones de validación para conservar los valores de los campos
    
    // Modificar la función clearErrors para que no limpie los campos
    function clearErrors() {
        const errorElements = document.querySelectorAll('.field-error');
        const inputElements = document.querySelectorAll('.form-group input:not([type="password"])');
        
        errorElements.forEach(element => {
            element.innerHTML = '';
            element.style.display = 'none';
        });
        
        // Limpiar clases de error/éxito pero mantener los valores
        inputElements.forEach(element => {
            element.classList.remove('error-field');
            element.classList.remove('success-field');
        });
        
        // Limpiar campos de contraseña por seguridad
        const passwordFields = document.querySelectorAll('input[type="password"]');
        passwordFields.forEach(field => {
            field.value = '';
            field.classList.remove('error-field');
            field.classList.remove('success-field');
        });
    }
    
    // Si el formulario tiene errores de validación del lado del cliente,
    // guarda los valores en localStorage para recuperarlos si la página se recarga
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (e.defaultPrevented) {
                // Si el formulario no se envió por errores de validación
                // Guardar los valores actuales (excepto contraseñas)
                if (regNombre) localStorage.setItem('reg_nombre', regNombre.value);
                if (regEmail) localStorage.setItem('reg_email', regEmail.value);
                if (regDireccion) localStorage.setItem('reg_direccion', regDireccion.value);
                if (regTelefono) localStorage.setItem('reg_telefono', regTelefono.value);
            } else {
                // Si el formulario se envía correctamente, limpiar localStorage
                localStorage.removeItem('reg_nombre');
                localStorage.removeItem('reg_email');
                localStorage.removeItem('reg_direccion');
                localStorage.removeItem('reg_telefono');
            }
        });
        
        // Al cargar la página, si hay valores en localStorage y los campos están vacíos, recuperarlos
        window.addEventListener('load', function() {
            // Solo recuperar de localStorage si los campos no tienen valores del servidor
            if (regNombre && regNombre.value === '' && localStorage.getItem('reg_nombre')) {
                regNombre.value = localStorage.getItem('reg_nombre');
            }
            if (regEmail && regEmail.value === '' && localStorage.getItem('reg_email')) {
                regEmail.value = localStorage.getItem('reg_email');
            }
            if (regDireccion && regDireccion.value === '' && localStorage.getItem('reg_direccion')) {
                regDireccion.value = localStorage.getItem('reg_direccion');
            }
            if (regTelefono && regTelefono.value === '' && localStorage.getItem('reg_telefono')) {
                regTelefono.value = localStorage.getItem('reg_telefono');
            }
        });
    }
});