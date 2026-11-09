document.addEventListener('DOMContentLoaded', function() {
    const userForm = document.getElementById('admin-add-user-form');
    
    if (!userForm) return; // Salir si no encontramos el formulario
    
    // Referencias a los campos
    const nombreInput = document.getElementById('nombre');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const password2Input = document.getElementById('password2');
    const telefonoInput = document.getElementById('telefono');
    
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
            let errorMsg = 'La contraseña debe cumplir los siguientes requisitos:';
            if (password.length < 8) errorMsg += '<br>- Al menos 8 caracteres';
            if (!/[A-Z]/.test(password)) errorMsg += '<br>- Al menos una letra mayúscula';
            if (!/[a-z]/.test(password)) errorMsg += '<br>- Al menos una letra minúscula';
            if (!/\d/.test(password)) errorMsg += '<br>- Al menos un número';
            if (!/[^\da-zA-Z]/.test(password)) errorMsg += '<br>- Al menos un carácter especial';
            
            return { valid: false, message: errorMsg };
        }
        
        return { valid: true };
    }
    
    function validateTelefono(telefono) {
        if (!telefono) return true; // Es opcional
        const regex = /^6[0-9]{8}$/;
        return regex.test(telefono);
    }
    
    // Función para verificar si hay caracteres no permitidos
    function validateAllowedCharacters(input, errorElementId) {
        const value = input.value;
        const notAllowedChars = value.match(/[áàäâéèëêíìïîóòöôúùüûñÁÀÄÂÉÈËÊÍÌÏÎÓÒÖÔÚÙÜÛÑ]/g);
        
        if (notAllowedChars) {
            showError(errorElementId, `Caracteres no permitidos: ${notAllowedChars.join(', ')}. No se permiten acentos, diéresis o ñ.`);
            return false;
        }
        return true;
    }
    
    // Función para validar coincidencia de contraseñas
    function validatePasswordMatch() {
        if (!passwordInput || !password2Input || password2Input.value === '') return true;
        
        if (passwordInput.value !== password2Input.value) {
            showError('password2-error', 'Las contraseñas no coinciden');
            return false;
        } else {
            clearError('password2-error');
            return true;
        }
    }
    
    // Funciones para mostrar y limpiar errores
    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.innerHTML = message;
            errorElement.style.display = 'block';
        }
    }
    
    function clearError(elementId) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.innerHTML = '';
            errorElement.style.display = 'none';
        }
    }
    
    // Validación en tiempo real para el nombre
    if (nombreInput) {
        nombreInput.addEventListener('input', function() {
            validateAllowedCharacters(this, 'nombre-error');
        });
        
        nombreInput.addEventListener('blur', function() {
            const nombre = this.value.trim();
            if (nombre === '') {
                showError('nombre-error', 'El nombre es obligatorio');
            } else if (!validateNombre(nombre)) {
                showError('nombre-error', 'El nombre debe tener máximo 16 caracteres y solo puede contener letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos.');
            } else {
                clearError('nombre-error');
            }
        });
    }
    
    // Validación en tiempo real para email
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            const email = this.value.trim();
            if (email === '') {
                showError('email-error', 'El email es obligatorio');
            } else if (!validateEmail(email)) {
                showError('email-error', 'Por favor, introduce un email válido');
            } else {
                clearError('email-error');
            }
        });
    }
    
    // Validación en tiempo real para contraseña
    if (passwordInput) {
        passwordInput.addEventListener('blur', function() {
            const password = this.value;
            if (password === '') {
                showError('password-error', 'La contraseña es obligatoria');
            } else {
                const validation = validatePassword(password);
                if (!validation.valid) {
                    showError('password-error', validation.message);
                } else {
                    clearError('password-error');
                    
                    // Si el campo de confirmación ya tiene contenido, verificar coincidencia
                    if (password2Input && password2Input.value !== '') {
                        validatePasswordMatch();
                    }
                }
            }
        });
        
        // Validar en tiempo real
        passwordInput.addEventListener('input', function() {
            if (password2Input && password2Input.value !== '') {
                validatePasswordMatch();
            }
        });
    }
    
    // Validación en tiempo real para confirmación de contraseña
    if (password2Input) {
        // Validar en tiempo real mientras se escribe
        password2Input.addEventListener('input', validatePasswordMatch);
        
        // Validar al perder el foco
        password2Input.addEventListener('blur', function() {
            if (this.value === '') {
                showError('password2-error', 'Por favor, confirma tu contraseña');
            } else {
                validatePasswordMatch();
            }
        });
    }
    
    // Validación en tiempo real para teléfono
    if (telefonoInput) {
        telefonoInput.addEventListener('blur', function() {
            const telefono = this.value.trim();
            // Solo validar si se ha introducido algo (ya que es opcional)
            if (telefono !== '' && !validateTelefono(telefono)) {
                showError('telefono-error', 'Introduce un número de teléfono móvil válido (9 dígitos que comience por 6)');
            } else {
                clearError('telefono-error');
            }
        });
    }
    
    // Validación al enviar el formulario
    userForm.addEventListener('submit', function(e) {
        // Limpiar todos los mensajes de error previos
        clearError('nombre-error');
        clearError('email-error');
        clearError('password-error');
        clearError('password2-error');
        clearError('telefono-error');
        
        // Validar todos los campos
        let hasErrors = false;
        
        // Validación de nombre
        const nombre = nombreInput.value.trim();
        if (nombre === '') {
            showError('nombre-error', 'El nombre es obligatorio');
            hasErrors = true;
        } else if (!validateNombre(nombre)) {
            showError('nombre-error', 'El nombre debe tener máximo 16 caracteres y solo puede contener letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos.');
            hasErrors = true;
        }
        
        // Validación de email
        const email = emailInput.value.trim();
        if (email === '') {
            showError('email-error', 'El email es obligatorio');
            hasErrors = true;
        } else if (!validateEmail(email)) {
            showError('email-error', 'Por favor, introduce un email válido');
            hasErrors = true;
        }
        
        // Validación de contraseña
        const password = passwordInput.value;
        if (password === '') {
            showError('password-error', 'La contraseña es obligatoria');
            hasErrors = true;
        } else {
            const validation = validatePassword(password);
            if (!validation.valid) {
                showError('password-error', validation.message);
                hasErrors = true;
            }
        }
        
        // Validación de confirmación de contraseña
        const password2 = password2Input.value;
        if (password2 === '') {
            showError('password2-error', 'Por favor, confirma tu contraseña');
            hasErrors = true;
        } else if (password !== password2) {
            showError('password2-error', 'Las contraseñas no coinciden');
            hasErrors = true;
        }
        
        // Validación de teléfono (opcional)
        const telefono = telefonoInput.value.trim();
        if (telefono !== '' && !validateTelefono(telefono)) {
            showError('telefono-error', 'Introduce un número de teléfono válido (9 dígitos que comience por 6)');
            hasErrors = true;
        }
        
        // Si hay errores, prevenir envío del formulario
        if (hasErrors) {
            e.preventDefault();
        }
    });
});