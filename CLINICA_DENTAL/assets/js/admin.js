document.addEventListener('DOMContentLoaded', function() {
    // Botones del panel de administración
    const btnAddUser = document.getElementById('btn-add-user');
    const btnDeleteUser = document.getElementById('btn-delete-user');
    const btnAddProduct = document.getElementById('btn-add-product');
    const btnEditProduct = document.getElementById('btn-edit-product');
    const btnDeleteProduct = document.getElementById('btn-delete-product');
    
    // Formularios
    const addUserFormContainer = document.getElementById('add-user-form');
    const addProductFormContainer = document.getElementById('add-product-form');
    
    // Verificar que los botones existen antes de añadir event listeners
    console.log('Botones de administración encontrados:', {
        btnAddUser: !!btnAddUser,
        btnDeleteUser: !!btnDeleteUser,
        btnAddProduct: !!btnAddProduct,
        btnEditProduct: !!btnEditProduct,
        btnDeleteProduct: !!btnDeleteProduct
    });
    
    if (btnAddUser && addUserFormContainer) {
        btnAddUser.addEventListener('click', function(e) {
            console.log('Click en añadir usuario');
            e.preventDefault();
            toggleElement(addUserFormContainer);
        });
    }
    
    if (btnDeleteUser) {
        const deleteUserList = document.getElementById('delete-user-list');
        if (deleteUserList) {
            btnDeleteUser.addEventListener('click', function(e) {
                console.log('Click en eliminar usuario');
                e.preventDefault();
                toggleElement(deleteUserList);
            });
        }
    }
    
    if (btnAddProduct && addProductFormContainer) {
        btnAddProduct.addEventListener('click', function(e) {
            console.log('Click en añadir producto');
            e.preventDefault();
            toggleElement(addProductFormContainer);
        });
    }
    
    if (btnEditProduct) {
        const editProductList = document.getElementById('edit-product-list');
        if (editProductList) {
            btnEditProduct.addEventListener('click', function(e) {
                console.log('Click en editar producto');
                e.preventDefault();
                toggleElement(editProductList);
            });
        }
    }
    
    if (btnDeleteProduct) {
        const deleteProductList = document.getElementById('delete-product-list');
        if (deleteProductList) {
            btnDeleteProduct.addEventListener('click', function(e) {
                console.log('Click en eliminar producto');
                e.preventDefault();
                toggleElement(deleteProductList);
            });
        }
    }
    
    // Funciones de utilidad
    function toggleElement(element) {
        if (element) {
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
                // Añadimos animación de aparición
                element.classList.add('fade-in');
                // Scroll suave hacia el elemento
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                element.style.display = 'none';
                element.classList.remove('fade-in');
            }
        }
    }
    
    function hideElement(element) {
        if (element) {
            element.style.display = 'none';
        }
    }
    
    // Manejar eliminación de usuarios - MEJORADO
    const deleteUserBtns = document.querySelectorAll('.btn-delete-confirm');
    console.log('Botones eliminar usuario encontrados:', deleteUserBtns.length);
    
    deleteUserBtns.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.id;
            const userName = this.closest('li').textContent.split('(')[0].trim();
            console.log('Click en eliminar usuario ID:', userId);
            
            // Mostrar modal de confirmación personalizado en lugar de confirm()
            showDeleteConfirmationModal({
                title: 'Eliminar Usuario',
                message: `¿Estás seguro de que deseas eliminar al usuario "${userName}"? Esta acción no se puede deshacer.`,
                confirmText: 'Eliminar',
                cancelText: 'Cancelar',
                type: 'danger',
                onConfirm: function() {
                    deleteUser(userId);
                }
            });
        });
    });
    
    // Manejar eliminación de productos - MEJORADO
    const deleteProductBtns = document.querySelectorAll('.btn-delete-product-confirm');
    console.log('Botones eliminar producto encontrados:', deleteProductBtns.length);
    
    deleteProductBtns.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.id;
            const productName = this.closest('li').textContent.split('(')[0].trim();
            console.log('Click en eliminar producto ID:', productId);
            
            // Mostrar modal de confirmación personalizado en lugar de confirm()
            showDeleteConfirmationModal({
                title: 'Eliminar Producto',
                message: `¿Estás seguro de que deseas eliminar el producto "${productName}"? Esta acción no se puede deshacer.`,
                confirmText: 'Eliminar',
                cancelText: 'Cancelar',
                type: 'danger',
                onConfirm: function() {
                    deleteProduct(productId);
                }
            });
        });
    });

    // Función para crear un modal de confirmación personalizado - SIN ICONOS
    function showDeleteConfirmationModal(options) {
        // Crear el modal
        const modal = document.createElement('div');
        modal.className = 'user-data-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${options.title}</h3>
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
    
    // Función para mostrar notificación de error personalizado
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

    // Función para eliminar usuario (separada para mejor organización)
    function deleteUser(userId) {
        // Referencia a la lista para ocultarla después
        const userListElement = document.getElementById('delete-user-list');
        
        // Enviar solicitud para eliminar el usuario
        fetch(SITE_URL + '/api/administrative-apis/delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ocultar la lista inmediatamente
                if (userListElement) {
                    userListElement.style.display = 'none';
                }
                
                // Mostrar modal de éxito
                showSuccessModal(
                    "¡Operación Exitosa!", 
                    "El usuario ha sido eliminado correctamente.",
                    () => {
                        // Callback cuando se cierra el modal (automática o manualmente)
                        window.location.reload();
                    }
                );
            } else {
                // Mostrar error con modal personalizado
                showErrorNotification(data.message || 'Error al eliminar el usuario.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Reemplazar alert con modal de error
            showErrorNotification('Error al procesar la solicitud.');
        });
    }

    // Función para eliminar producto (separada para mejor organización)
    function deleteProduct(productId) {
        // Mostrar indicador de carga
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = '<span>Eliminando producto...</span>';
        document.body.appendChild(loadingIndicator);
        
        // Enviar solicitud para eliminar el producto
        fetch(SITE_URL + '/api/administrative-apis/delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: productId })
        })
        .then(response => {
            // Primero verificar si la respuesta es válida
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.text().then(text => {
                try {
                    // Intentar parsear como JSON
                    return text ? JSON.parse(text) : {};
                } catch (e) {
                    console.error('Error al parsear respuesta JSON:', text);
                    throw new Error('Respuesta no válida del servidor');
                }
            });
        })
        .then(data => {
            // Eliminar indicador de carga
            if (loadingIndicator) {
                loadingIndicator.remove();
            }
            
            // Mostrar modal de éxito
            showSuccessModal(
                "¡Operación Exitosa!", 
                "El producto ha sido eliminado correctamente.",
                () => {
                    // Callback cuando se cierra el modal (automática o manualmente)
                    window.location.reload();
                }
            );
        })
        .catch(error => {
            // Eliminar indicador de carga
            if (loadingIndicator) {
                loadingIndicator.remove();
            }
            
            console.error('Error completo:', error);
            
            // Aún así recargar la página, ya que probablemente el producto se eliminó
            const errorMessage = document.createElement('div');
            errorMessage.className = 'alert alert-warning fixed-top';
            errorMessage.style.zIndex = '9999';
            errorMessage.innerHTML = 'La operación pudo haber sido completada.<br>Recargando la página...';
            document.body.appendChild(errorMessage);
            
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        });
    }
    
    // Previsualización de la imagen del producto
    const imageInput = document.getElementById('imagen');
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const existingPreview = document.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                    
                    const preview = document.createElement('div');
                    preview.className = 'image-preview';
                    preview.innerHTML = `
                        <h4>Vista previa:</h4>
                        <img src="${e.target.result}" alt="Vista previa" style="max-width: 200px; max-height: 200px; margin-top: 10px;">
                    `;
                    
                    imageInput.parentNode.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Formulario de añadir usuario
    if (addUserFormContainer) {
        const formAddUser = addUserFormContainer.querySelector('form');
        if (formAddUser) {
            formAddUser.addEventListener('submit', function(e) {
                let hasErrors = false;
                
                // Obtener valores de los campos
                const nombre = document.getElementById('nombre');
                const email = document.getElementById('email');
                const password = document.getElementById('password');
                const telefono = document.getElementById('telefono');
                
                // Validaciones solo si los elementos existen
                if (nombre && email && password) {
                    // Resto del código de validación...
                }
            });
        }
    }
    
    // Formulario de añadir producto
    if (addProductFormContainer) {
        const formAddProduct = addProductFormContainer.querySelector('form');
        if (formAddProduct) {
            formAddProduct.addEventListener('submit', function() {
                // Cambia el texto del botón cuando se envía
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.textContent = 'Guardando...';
                    submitButton.disabled = true;
                }
            });
        }
    }

    // Hacer que las alertas existentes desaparezcan automáticamente
    const existingAlerts = document.querySelectorAll('.alert');
    if (existingAlerts.length > 0) {
        existingAlerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('fade-out');
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 3000);
        });
    }

    // Para el formulario de añadir producto - Usar la referencia única
    const precioInput = document.getElementById('precio');
    const stockInput = document.getElementById('stock');
    
    // Validación para campo de stock
    if (stockInput) {
        // Agregar mensaje de error debajo del campo
        const stockErrorMsg = document.createElement('span');
        stockErrorMsg.className = 'field-error';
        stockErrorMsg.id = 'stock-error';
        stockErrorMsg.style.display = 'none';
        stockErrorMsg.style.color = '#dc3545';
        stockErrorMsg.style.fontSize = '0.8rem';
        stockErrorMsg.style.marginTop = '5px';
        stockInput.parentNode.appendChild(stockErrorMsg);
        
        // Limitar a solo números y máximo 6 cifras
        stockInput.addEventListener('keypress', function(e) {
            const charCode = (e.which) ? e.which : e.keyCode;
            
            // Permitir solo números
            if (charCode < 48 || charCode > 57) {
                e.preventDefault();
                return false;
            }
            
            // Limitar a 6 dígitos (cambiado de 5 a 6)
            if (this.value.length >= 6) {
                e.preventDefault();
                return false;
            }
            
            return true;
        });
        
        // Validar al perder el foco
        stockInput.addEventListener('blur', function() {
            const stockValue = this.value.trim();
            
            if (stockValue === '') {
                showInputError(stockErrorMsg, 'El stock es obligatorio');
            } else if (!/^\d{1,6}$/.test(stockValue)) {
                showInputError(stockErrorMsg, 'El stock debe ser un número entero de hasta 6 dígitos');
            } else if (parseInt(stockValue) < 0) {
                showInputError(stockErrorMsg, 'El stock no puede ser negativo');
            } else {
                hideInputError(stockErrorMsg);
            }
        });
        
        // Limpiar mensaje de error al escribir
        stockInput.addEventListener('input', function() {
            hideInputError(stockErrorMsg);
        });
        
        // Añadir texto explicativo debajo del campo stock
        const stockHelpText = document.createElement('small');
        stockHelpText.className = 'input-help';
        stockHelpText.textContent = 'Solo números, máximo 6 cifras';
        stockInput.parentNode.insertBefore(stockHelpText, stockErrorMsg);
        
        // Validar y limpiar cualquier carácter no numérico en tiempo real
        stockInput.addEventListener('input', function() {
            // Limpiar cualquier caracter que no sea número
            this.value = this.value.replace(/[^\d]/g, '');
            
            // Validar longitud máxima también aquí (por si se pega un texto largo)
            if (this.value.length > 6) {
                this.value = this.value.substring(0, 6);
            }
            
            hideInputError(stockErrorMsg);
        });
    }
    
    // Validación para campo de precio
    if (precioInput) {
        // Agregar mensaje de error debajo del campo
        const precioErrorMsg = document.createElement('span');
        precioErrorMsg.className = 'field-error';
        precioErrorMsg.id = 'precio-error';
        precioErrorMsg.style.display = 'none';
        precioErrorMsg.style.color = '#dc3545';
        precioErrorMsg.style.fontSize = '0.8rem';
        precioErrorMsg.style.marginTop = '5px';
        precioInput.parentNode.appendChild(precioErrorMsg);
        
        // Limitar a solo números, coma y máximo 6 caracteres
        precioInput.addEventListener('keypress', function(e) {
            const charCode = (e.which) ? e.which : e.keyCode;
            const value = this.value;
            
            // Permitir solo números y coma
            if (charCode !== 44 && (charCode < 48 || charCode > 57)) {
                e.preventDefault();
                return false;
            }
            
            // Permitir solo una coma
            if (charCode === 44 && value.includes(',')) {
                e.preventDefault();
                return false;
            }
            
            // Limitar a 6 caracteres en total
            if (value.length >= 6 && !isCaretAfterComma(this)) {
                e.preventDefault();
                return false;
            }
            
            // Limitar a 2 decimales después de la coma
            if (value.includes(',')) {
                const partes = value.split(',');
                if (partes[1] && partes[1].length >= 2 && isCaretAfterComma(this)) {
                    e.preventDefault();
                    return false;
                }
            }
            
            return true;
        });
        
        // Formatear precio al perder el foco
        precioInput.addEventListener('blur', function() {
            const precioValue = this.value.trim();
            
            if (precioValue === '') {
                showInputError(precioErrorMsg, 'El precio es obligatorio');
                return;
            }
            
            if (!/^\d{1,3}(,\d{0,2})?$/.test(precioValue)) {
                showInputError(precioErrorMsg, 'El precio debe tener el formato correcto (ej: 123,45)');
                return;
            }
            
            // Formatear con dos decimales
            if (precioValue.includes(',')) {
                const partes = precioValue.split(',');
                if (partes[1].length === 1) {
                    // Añadir un cero si solo hay un decimal
                    this.value = `${partes[0]},${partes[1]}0`;
                }
            } else if (precioValue !== '') {
                // Si no tiene decimales, añadir ,00
                this.value = `${precioValue},00`;
            }
            
            hideInputError(precioErrorMsg);
        });
        
        // Limpiar mensaje de error al escribir
        precioInput.addEventListener('input', function() {
            hideInputError(precioErrorMsg);
        });
        
        // Añadir texto explicativo debajo del campo precio
        const precioHelpText = document.createElement('small');
        precioHelpText.className = 'input-help';
        precioHelpText.textContent = 'Solo números, una coma y máximo 2 decimales (ej: 123,45)';
        precioInput.parentNode.insertBefore(precioHelpText, precioErrorMsg);
        
        // Validar y limpiar cualquier carácter no permitido en tiempo real para el precio
        precioInput.addEventListener('input', function() {
            // Limpiar cualquier caracter que no sea número o coma
            this.value = this.value.replace(/[^\d,]/g, '');
            
            // Asegurar que solo haya una coma
            const parts = this.value.split(',');
            if (parts.length > 2) {
                this.value = parts[0] + ',' + parts.slice(1).join('');
            }
            
            // Limitar a 2 decimales después de la coma
            if (parts.length > 1 && parts[1].length > 2) {
                this.value = parts[0] + ',' + parts[1].substring(0, 2);
            }
            
            hideInputError(precioErrorMsg);
        });
    }
    
    // Función para determinar si el cursor está después de la coma
    function isCaretAfterComma(input) {
        const caretPos = input.selectionStart;
        const commaPos = input.value.indexOf(',');
        return commaPos !== -1 && caretPos > commaPos;
    }
    
    // Función para mostrar error en un campo
    function showInputError(errorElement, message) {
        if (!errorElement) return;
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
        
        // Obtener el campo de entrada relacionado
        const inputField = errorElement.previousElementSibling;
        if (inputField) {
            inputField.classList.add('error-field');
        }
    }
    
    // Función para ocultar error en un campo
    function hideInputError(errorElement) {
        if (!errorElement) return;
        
        errorElement.textContent = '';
        errorElement.style.display = 'none';
        
        // Obtener el campo de entrada relacionado
        const inputField = errorElement.previousElementSibling;
        if (inputField) {
            inputField.classList.remove('error-field');
        }
    }

    // Función para mostrar notificación de éxito personalizada
    function showSuccessModal(title, message, callback) {
        // Crear el modal
        const modal = document.createElement('div');
        modal.className = 'user-data-modal success-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${title}</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="success-icon-container" style="text-align: center;">
                        <i class="fas fa-check-circle" style="font-size: 4rem; color: #28a745; margin-bottom: 20px; display: inline-block;"></i>
                    </div>
                    <p style="text-align: center; font-size: 1.1em;">${message}</p>
                    <div class="confirmation-buttons">
                        <button class="btn btn-primary" id="success-ok">Aceptar</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Añadir animación de entrada
        setTimeout(() => {
            modal.querySelector('.modal-content').style.transform = 'scale(1)';
            modal.querySelector('.modal-content').style.opacity = '1';
        }, 10);
        
        // Manejar cierre del modal
        const closeBtn = modal.querySelector('.close-modal');
        closeBtn.addEventListener('click', function() {
            closeModalWithAnimation();
        });
        
        // Manejar botón de aceptar
        const okBtn = modal.querySelector('#success-ok');
        okBtn.addEventListener('click', function() {
            closeModalWithAnimation();
        });
        
        // Auto-cerrar después de 3 segundos
        const autoCloseTimeout = setTimeout(() => {
            if (document.body.contains(modal)) {
                closeModalWithAnimation();
            }
        }, 3000);
        
        // Función para cerrar con animación
        function closeModalWithAnimation() {
            clearTimeout(autoCloseTimeout); // Evitar que se cierre dos veces
            
            // Animar salida
            const modalContent = modal.querySelector('.modal-content');
            modalContent.style.transform = 'scale(0.8)';
            modalContent.style.opacity = '0';
            
            setTimeout(() => {
                modal.remove();
                // Ejecutar callback si existe
                if (typeof callback === 'function') {
                    callback();
                }
            }, 300);
        }
    }
    
    // Función para formatear precio (era llamada pero no estaba definida)
    function formatPrice(input) {
        // Reemplazar punto por coma si es necesario
        let value = input.value.replace('.', ',');
        
        // Si tiene decimales, asegurarse de que tenga máximo 2
        if (value.includes(',')) {
            const parts = value.split(',');
            if (parts[1].length > 2) {
                parts[1] = parts[1].substring(0, 2);
                value = parts.join(',');
            }
            // Añadir un cero si solo hay un decimal
            if (parts[1].length === 1) {
                value = `${parts[0]},${parts[1]}0`;
            }
        } else if (value !== '') {
            // Si no tiene decimales, añadir ,00
            value = `${value},00`;
        }
        
        input.value = value;
    }
    
    // Función para validar contraseña
    function validatePassword(password) {
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

    // Función para validar nombre de usuario
    function validateNombre(nombre) {
        // Solo letras sin acentos, números, guiones y guiones bajos, máximo 16 caracteres
        const regex = /^[a-zA-Z0-9_-]{1,16}$/;
        return regex.test(nombre);
    }

    // Función para validar caracteres no permitidos
    function validateAllowedCharacters(input) {
        const value = input.value;
        const notAllowedChars = value.match(/[áàäâéèëêíìïîóòöôúùüûñÁÀÄÂÉÈËÊÍÌÏÎÓÒÖÔÚÙÜÛÑ]/g);
        
        if (notAllowedChars) {
            const errorMsg = `Caracteres no permitidos: ${notAllowedChars.join(', ')}. No se permiten acentos, diéresis o ñ.`;
            showInputError(input.nextElementSibling, errorMsg);
            return false;
        }
        return true;
    }

    // Actualizar el código dentro del bloque para el formulario de añadir usuario
    if (formAddUser) {
        // Obtener los campos
        const nombreInput = document.getElementById('nombre');
        const passwordInput = document.getElementById('password');
        
        // Añadir validación en tiempo real para caracteres no permitidos en el nombre
        if (nombreInput) {
            nombreInput.addEventListener('input', function() {
                validateAllowedCharacters(this);
            });
            
            nombreInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    showInputError(this.nextElementSibling, 'El nombre es obligatorio');
                } else if (!validateNombre(this.value)) {
                    showInputError(this.nextElementSibling, 'El nombre debe tener máximo 16 caracteres y solo puede contener letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos.');
                } else {
                    hideInputError(this.nextElementSibling);
                }
            });
        }
        
        // Añadir validación para la contraseña
        if (passwordInput) {
            passwordInput.addEventListener('blur', function() {
                if (this.value.trim() === '') {
                    showInputError(this.nextElementSibling, 'La contraseña es obligatoria');
                } else {
                    const validation = validatePassword(this.value);
                    if (!validation.valid) {
                        showInputError(this.nextElementSibling, validation.message);
                    } else {
                        hideInputError(this.nextElementSibling);
                    }
                }
            });
        }
        
        // Validación al enviar el formulario
        formAddUser.addEventListener('submit', function(e) {
            let hasErrors = false;
            
            // Validar nombre
            if (nombreInput && (nombreInput.value.trim() === '' || !validateNombre(nombreInput.value))) {
                e.preventDefault();
                hasErrors = true;
                showInputError(nombreInput.nextElementSibling, 'El nombre debe tener máximo 16 caracteres y solo puede contener letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos.');
            }
            
            // Validar contraseña
            if (passwordInput) {
                const validation = validatePassword(passwordInput.value);
                if (!validation.valid) {
                    e.preventDefault();
                    hasErrors = true;
                    showInputError(passwordInput.nextElementSibling, validation.message);
                }
            }
            
            if (!hasErrors) {
                // Cambiar el texto del botón cuando se envía
                const submitButton = this.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.textContent = 'Guardando...';
                    submitButton.disabled = true;
                }
            }
        });
    }
});