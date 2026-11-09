document.addEventListener('DOMContentLoaded', function() {
    // Definir la URL base del sitio a partir del meta tag
    const SITE_URL = document.querySelector('meta[name="site-url"]').getAttribute('content');
    console.log('SITE_URL:', SITE_URL);
    
    // Actualizar contador del carrito al cargar la página
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }
    
    console.log('Inicializando botones del carrito...');
    
    // Seleccionar todos los botones del carrito
    const decreaseButtons = document.querySelectorAll('.quantity-decrease');
    const increaseButtons = document.querySelectorAll('.quantity-increase');
    const removeButtons = document.querySelectorAll('.btn-remove');
    const checkoutButton = document.getElementById('checkout-btn');
    const addToCartButtons = document.querySelectorAll('.agregar-al-carrito');
    
    console.log('Botones encontrados:', {
        decreaseButtons: decreaseButtons.length,
        increaseButtons: increaseButtons.length,
        removeButtons: removeButtons.length,
        checkoutButton: checkoutButton,
        addToCartButtons: addToCartButtons.length
    });
    
    // Manejar botones de disminuir cantidad
    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Click en botón disminuir');
            const cartId = this.dataset.id;
            const quantityElement = this.nextElementSibling;
            const currentQuantity = parseInt(quantityElement.textContent);
            const currentRow = this.closest('tr');
            const productId = currentRow.dataset.productId;
            const productName = currentRow.querySelector('.cart-product span').textContent;
            
            console.log('Disminuir cantidad:', {cartId, currentQuantity, productId});
            
            // Si la cantidad es 1, mostrar confirmación
            if (currentQuantity === 1) {
                const cartItems = document.querySelectorAll('.cart-table tbody tr');
                
                // Contar cuántos productos del mismo tipo hay en el carrito
                let productTypeCount = 0;
                document.querySelectorAll(`tr[data-product-id="${productId}"]`).forEach(() => {
                    productTypeCount++;
                });
                
                // Verificar qué tipo de confirmación mostrar
                if (cartItems.length === 1) {
                    // Si es el último producto del carrito
                    showLastProductRemovalConfirmation(cartId, productName, true, false);
                } else if (productTypeCount === 1) {
                    // Si es el último producto de ese tipo específico
                    showLastProductRemovalConfirmation(cartId, productName, false, true);
                } else {
                    // Si no es el último producto, actualizar normalmente
                    updateCartQuantity(cartId, -1);
                }
            } else {
                // Si tiene más de 1 unidad, actualizar normalmente
                updateCartQuantity(cartId, -1);
            }
        });
    });
    
    // Manejar botones de aumentar cantidad
    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Click en botón aumentar');
            const cartId = this.dataset.id;
            updateCartQuantity(cartId, 1);
        });
    });
    
    // Manejar botones de eliminar
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Click en botón eliminar');
            const cartId = this.dataset.id;
            removeFromCart(cartId);
        });
    });
    
    // Manejar botón de finalizar compra
    if (checkoutButton) {
        checkoutButton.addEventListener('click', function() {
            console.log('Click en botón finalizar compra');
            // Verificar si el usuario tiene dirección y teléfono
            fetch(SITE_URL + '/api/cart/check_user_data.php')
                .then(response => response.json())
                .then(data => {
                    if (data.hasRequiredData) {
                        // Si tiene los datos, mostrar confirmación
                        showCheckoutConfirmation();
                    } else {
                        // Si no tiene los datos, mostrar modal
                        showUserDataModalCheckout();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorNotification('Error al verificar tus datos. Por favor, intenta de nuevo.');
                });
        });
    }
    
    // Manejar botones de añadir al carrito (en la página de tienda)
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            console.log('Click en añadir al carrito');
            
            const productId = this.dataset.id;
            console.log('Producto ID:', productId);
            
            // Verificar si el usuario está logueado
            fetch(SITE_URL + '/api/authentication-apis/check-session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        // Si no está logueado, guardar el producto en localStorage para añadirlo después
                        localStorage.setItem('pending_product', productId);
                        // Redirigir a login con parámetros para redirección posterior
                        window.location.href = SITE_URL + '/pages/auth.php?action=login&require_login=true&redirect=shop';
                    } else {
                        // Si está logueado, añadir directamente al carrito
                        addProductToCart(productId);
                    }
                })
                .catch(error => {
                    console.error('Error al verificar sesión:', error);
                    showErrorNotification('Error al verificar tu sesión. Por favor, recarga la página.');
                });
        });
    });
    
    // Función para añadir producto al carrito
    function addProductToCart(productId, quantity = 1) {
        console.log('Añadiendo producto al carrito:', productId);
        
        fetch(SITE_URL + '/api/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador de carrito
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                
                // Mostrar notificación de éxito
                showSuccessNotification('¡Producto añadido al carrito!', true);
            } else {
                showErrorNotification(data.message || 'Error al añadir el producto');
            }
        })
        .catch(error => {
            console.error('Error al añadir producto:', error);
            showErrorNotification('Error en la conexión. Por favor, intenta más tarde.');
        });
    }
    
    // Función para actualizar cantidad
    function updateCartQuantity(cartId, change) {
        console.log('Actualizando cantidad:', {cartId, change});
        
        fetch(SITE_URL + '/api/cart/update_quantity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cart_id: cartId,
                change: change
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta update_quantity:', data);
            if (data.success) {
                // Actualizar contador y recargar
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                window.location.reload();
            } else {
                showErrorNotification(data.message || 'Error al actualizar la cantidad');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorNotification('Error al actualizar la cantidad. Por favor, intenta de nuevo.');
        });
    }
    
    // Función para eliminar producto
    function removeFromCart(cartId) {
        console.log('Eliminando producto:', cartId);
        
        const cartItems = document.querySelectorAll('.cart-table tbody tr');
        const currentRow = document.querySelector(`.btn-remove[data-id="${cartId}"]`).closest('tr');
        const productName = currentRow.querySelector('.cart-product span').textContent;
        const productId = currentRow.dataset.productId; // Asegúrate de que cada fila tenga un data-product-id
        
        // Contar cuántos productos del mismo tipo hay en el carrito
        let productTypeCount = 0;
        document.querySelectorAll(`tr[data-product-id="${productId}"]`).forEach(() => {
            productTypeCount++;
        });
        
        if (cartItems.length === 1) {
            // Si es el último producto del carrito
            showLastProductRemovalConfirmation(cartId, productName, true, false);
        } else if (productTypeCount === 1) {
            // Si es el último producto de ese tipo específico
            showLastProductRemovalConfirmation(cartId, productName, false, true);
        } else {
            // Si hay más productos del mismo tipo, eliminar directamente
            performRemoveFromCart(cartId);
        }
    }
    
    // Confirmación para eliminar último producto
    function showLastProductRemovalConfirmation(cartId, productName, isLastInCart, isLastOfType) {
        console.log('Mostrando confirmación para eliminar producto:', {cartId, productName, isLastInCart, isLastOfType});
        
        let modalTitle, modalMessage;
        
        if (isLastInCart) {
            modalTitle = 'Eliminar último producto';
            modalMessage = `<p>Este es el último producto en tu carrito. ¿Estás seguro de eliminarlo y vaciar tu carrito?</p>`;
        } else if (isLastOfType) {
            modalTitle = 'Eliminar último producto de este tipo';
            modalMessage = `<p>Este es el último "${productName}" en tu carrito. ¿Estás seguro de eliminarlo?</p>`;
        } else {
            modalTitle = 'Eliminar producto';
            modalMessage = `<p>¿Estás seguro de que deseas eliminar este producto?</p>`;
        }
        
        const modal = document.createElement('div');
        modal.className = 'user-data-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>${modalTitle}</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="product-info-box">
                        <p class="product-name"><strong>${productName}</strong></p>
                    </div>
                    ${modalMessage}
                    <div class="confirmation-buttons">
                        <button class="btn btn-secondary" id="cancel-delete">Cancelar</button>
                        <button class="btn btn-danger" id="confirm-delete">Eliminar</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Eventos de los botones del modal
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        modal.querySelector('#cancel-delete').addEventListener('click', () => modal.remove());
        modal.querySelector('#confirm-delete').addEventListener('click', () => {
            performRemoveFromCart(cartId);
            modal.remove();
        });
    }
    
    // Eliminar producto de la base de datos
    function performRemoveFromCart(cartId) {
        console.log('Ejecutando eliminación del producto:', cartId);
        
        fetch(SITE_URL + '/api/cart/remove.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ cart_id: cartId })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta remove.php:', data);
            if (data.success) {
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                window.location.reload();
            } else {
                showErrorNotification(data.message || 'Error al eliminar el producto');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorNotification('Error al eliminar el producto. Por favor, intenta de nuevo.');
        });
    }
    
    // Procesar compra
    function processCheckout() {
        console.log('Procesando compra...');
        
        fetch(SITE_URL + '/api/cart/checkout.php')
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta checkout.php:', data);
            if (data.success) {
                showSuccessCheckoutNotification(data.message);
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
            } else {
                showErrorNotification(data.message || 'Error al procesar la compra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorNotification('Error al procesar la compra. Por favor, intenta de nuevo.');
        });
    }
    
    // Notificación de compra exitosa
    function showSuccessCheckoutNotification(message) {
        console.log('Mostrando notificación de compra exitosa');
        
        const modal = document.createElement('div');
        modal.className = 'user-data-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>¡Compra realizada!</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="success-message-container">
                        <i class="fas fa-check-circle success-icon" style="font-size: 4rem; color: #28a745; margin: 0 auto 20px auto; display: block; text-align: center;"></i>
                        <p style="text-align: center; margin: 20px 0;">${message}</p>
                    </div>
                    <div class="confirmation-buttons" style="display: flex; justify-content: center;">
                        <button id="understand-button" class="btn btn-primary" style="min-width: 150px; padding: 12px 24px; background: linear-gradient(135deg, #0077cc, #005fa3); border: none; border-radius: 50px; box-shadow: 0 4px 10px rgba(0, 119, 204, 0.3); font-weight: 600; transition: all 0.3s ease;">Entendido</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Cerrar modal
        modal.querySelector('.close-modal').addEventListener('click', () => {
            modal.remove();
            window.location.href = `${SITE_URL}/pages/shop.php`;
        });
        
        // Manejar botón de entendido
        modal.querySelector('#understand-button').addEventListener('click', () => {
            modal.remove();
            window.location.href = `${SITE_URL}/pages/shop.php`;
        });
    }
    
    // Confirmación de checkout
    function showCheckoutConfirmation() {
        console.log('Mostrando confirmación de checkout');
        
        const modal = document.createElement('div');
        modal.className = 'user-data-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Confirmar compra</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres finalizar la compra con estos productos?</p>
                    <div class="confirmation-buttons">
                        <button class="btn btn-secondary" id="cancel-checkout">Cancelar</button>
                        <button class="btn btn-primary" id="confirm-checkout">Confirmar compra</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Eventos de los botones
        modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
        modal.querySelector('#cancel-checkout').addEventListener('click', () => modal.remove());
        modal.querySelector('#confirm-checkout').addEventListener('click', () => {
            modal.remove();
            processCheckout();
        });
    }
    
    // Modal para recoger datos de usuario
    function showUserDataModalCheckout() {
        console.log('Mostrando modal para datos de usuario');
        
        // Primero verificar qué datos específicos faltan
        fetch(SITE_URL + '/api/cart/check_user_data.php')
            .then(response => response.json())
            .then(data => {
                console.log('Datos de usuario recibidos:', data); // Añadir para depuración
                
                // Determinar explícitamente qué campos faltan
                const faltaDireccion = !data.hasDireccion;
                const faltaTelefono = !data.hasTelefono;
                
                console.log('Falta dirección:', faltaDireccion, 'Falta teléfono:', faltaTelefono); // Añadir para depuración
                
                // Si no falta nada, continuar directamente
                if (!faltaDireccion && !faltaTelefono) {
                    console.log('No faltan datos, mostrando confirmación directamente');
                    showCheckoutConfirmation();
                    return;
                }
                
                // Crear título y mensaje apropiados según lo que falta
                let titulo = 'Información de envío';
                let mensaje = 'Para finalizar tu compra, necesitamos la siguiente información:';
                
                // Crear el modal
                const modal = document.createElement('div');
                modal.className = 'user-data-modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>${titulo}</h3>
                            <span class="close-modal">&times;</span>
                        </div>
                        <div class="modal-body">
                            <p>${mensaje}</p>
                            <form id="user-data-form">
                                ${faltaDireccion ? `
                                <div class="form-group">
                                    <label for="modal-direccion">Dirección de entrega:</label>
                                    <input type="text" id="modal-direccion" name="direccion" required>
                                    <span class="field-error" id="modal-direccion-error"></span>
                                </div>
                                ` : ''}
                                ${faltaTelefono ? `
                                <div class="form-group">
                                    <label for="modal-telefono">Teléfono de contacto:</label>
                                    <input type="tel" inputmode="numeric" id="modal-telefono" name="telefono" required 
                                           maxlength="9" pattern="6[0-9]{8}">
                                    <small class="input-help">Debe ser un móvil válido (9 dígitos que comience por 6)</small>
                                    <span class="field-error" id="modal-telefono-error"></span>
                                </div>
                                ` : ''}
                                <button type="submit" class="btn btn-primary">Guardar y continuar</button>
                            </form>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                
                // Validación para el teléfono si es necesario
                if (faltaTelefono) {
                    const telefonoInput = modal.querySelector('#modal-telefono');
                    telefonoInput.addEventListener('keypress', function(e) {
                        return isNumberKey(e);
                    });
                }
                
                // Cerrar modal
                modal.querySelector('.close-modal').addEventListener('click', () => modal.remove());
                
                // Validar formulario
                modal.querySelector('#user-data-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    let hasErrors = false;
                    let datosActualizados = {};
                    
                    // Validar dirección si está presente
                    if (faltaDireccion) {
                        const direccion = document.getElementById('modal-direccion').value.trim();
                        if (direccion === '') {
                            showModalError('modal-direccion', 'La dirección es obligatoria');
                            hasErrors = true;
                        } else {
                            datosActualizados.direccion = direccion;
                        }
                    }
                    
                    // Validar teléfono si está presente
                    if (faltaTelefono) {
                        const telefono = document.getElementById('modal-telefono').value.trim();
                        if (telefono === '') {
                            showModalError('modal-telefono', 'El teléfono es obligatorio');
                            hasErrors = true;
                        } else if (!validateTelefono(telefono)) {
                            showModalError('modal-telefono', 'Introduce un número de teléfono móvil válido (9 dígitos que comience por 6)');
                            hasErrors = true;
                        } else {
                            datosActualizados.telefono = telefono;
                        }
                    }
                    
                    if (!hasErrors) {
                        // Guardar datos y continuar
                        fetch(SITE_URL + '/api/cart/update_user_data.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify(datosActualizados)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                modal.remove();
                                showCheckoutConfirmation();
                            } else {
                                showErrorNotification('Error al guardar la información: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showErrorNotification('Error al guardar la información. Por favor, intenta de nuevo.');
                        });
                    }
                });
            })
            .catch(error => {
                console.error('Error al verificar datos del usuario:', error);
                showErrorNotification('Error al verificar tus datos. Por favor, intenta de nuevo.');
            });
    }
    
    // Función para permitir solo teclas numéricas
    function isNumberKey(evt) {
        const charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            evt.preventDefault();
            return false;
        }
        return true;
    }
    
    // Validar teléfono
    function validateTelefono(telefono) {
        const regex = /^6[0-9]{8}$/;
        return regex.test(telefono);
    }
    
    // Mostrar error en el modal
    function showModalError(elementId, message) {
        const input = document.getElementById(elementId);
        if (!input) return;
        
        const errorSpan = document.getElementById(`${elementId}-error`);
        if (errorSpan) {
            errorSpan.textContent = message;
        }
        
        input.classList.remove('success-field');
        input.classList.add('error-field');
    }
    
    // Mostrar notificación de error
    function showErrorNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification error';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
            </div>
            <button class="close-notification">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            notification.classList.add('fadeOut');
            setTimeout(() => notification.remove(), 500);
        }, 5000);
        
        // Botón cerrar
        notification.querySelector('.close-notification').addEventListener('click', () => {
            notification.classList.add('fadeOut');
            setTimeout(() => notification.remove(), 500);
        });
    }
    
    // Mostrar notificación de éxito
    function showSuccessNotification(message, autoRedirect = false) {
        const notification = document.createElement('div');
        notification.className = 'notification success';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            </div>
            <button class="close-notification">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-cerrar después de 3 segundos
        setTimeout(() => {
            notification.classList.add('fadeOut');
            setTimeout(() => {
                notification.remove();
                if (autoRedirect) {
                    window.location.href = SITE_URL + '/pages/cart.php';
                }
            }, 500);
        }, 3000);
        
        // Botón cerrar
        notification.querySelector('.close-notification').addEventListener('click', () => {
            notification.classList.add('fadeOut');
            setTimeout(() => {
                notification.remove();
                if (autoRedirect) {
                    window.location.href = SITE_URL + '/pages/cart.php';
                }
            }, 500);
        });
    }
});