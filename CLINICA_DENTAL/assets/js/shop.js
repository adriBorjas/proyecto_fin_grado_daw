document.addEventListener('DOMContentLoaded', function() {
    // Obtener SITE_URL de la meta etiqueta
    const siteUrlMeta = document.querySelector('meta[name="site-url"]');
    if (siteUrlMeta) {
        SITE_URL = siteUrlMeta.getAttribute('content');
    } else {
        // Fallback
        SITE_URL = window.location.origin + '/CLINICA_DENTAL';
    }
    
    // Verificar si hay un producto pendiente de añadir al carrito
    const pendingProduct = localStorage.getItem('pending_product');
    const pendingQuantity = localStorage.getItem('pending_quantity') || 1;
    const loginSuccess = new URLSearchParams(window.location.search).get('login_success') === 'true';
    const registerSuccess = new URLSearchParams(window.location.search).get('register_success') === 'true';

    // AÑADIR ESTA CONDICIÓN PARA REGISTRO
    if (pendingProduct && (loginSuccess || registerSuccess)) {
        console.log('Producto pendiente encontrado:', pendingProduct);
        // Eliminar el producto pendiente del localStorage
        localStorage.removeItem('pending_product');
        localStorage.removeItem('pending_quantity');
        
        // Añadir el producto al carrito
        addProductToCart(pendingProduct, parseInt(pendingQuantity));
        
        // Limpiar parámetros de URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // Actualizar contador de carrito al cargar la página
    updateCartCount();
    
    // Manejar añadir productos al carrito
    const addToCartButtons = document.querySelectorAll('.agregar-al-carrito');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            
            const productId = this.dataset.id;
            console.log('Intentando añadir producto:', productId);
            
            // Verificar si el usuario está logueado
            fetch(SITE_URL + '/api/authentication-apis/check-session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        console.log('Usuario no logueado, guardando producto pendiente');
                        // Si no está logueado, guardar el producto en localStorage para añadirlo después
                        localStorage.setItem('pending_product', productId);
                        localStorage.setItem('pending_quantity', 1);
                        // Redirigir a login con parámetros para redirección posterior
                        window.location.href = SITE_URL + '/pages/auth.php?action=login&require_login=true&redirect=shop';
                    } else {
                        console.log('Usuario logueado, añadiendo producto directamente');
                        // Si está logueado, añadir directamente al carrito
                        addProductToCart(productId);
                    }
                })
                .catch(error => {
                    console.error('Error al verificar sesión:', error);
                    // En lugar de mostrar error, simplemente redirigir al login
                    localStorage.setItem('pending_product', productId);
                    localStorage.setItem('pending_quantity', 1);
                    window.location.href = SITE_URL + '/pages/auth.php?action=login&require_login=true&redirect=shop';
                });
        });
    });
    
    // Función para añadir producto al carrito
    function addProductToCart(productId, quantity) {
        console.log('Añadiendo producto al carrito:', productId, 'cantidad:', quantity || 1);
        
        fetch(SITE_URL + '/api/cart/add.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity || 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador de carrito
                updateCartCount();
                
                // Mostrar notificación de éxito
                showSuccessNotification('¡Producto añadido al carrito!', true);
                
                // Recargar la página para actualizar el stock
                setTimeout(() => {
                    window.location.reload();
                }, 1500); // Esperar 1.5 segundos para que el usuario vea la notificación
                
            } else {
                showErrorNotification(data.message || 'Error al añadir el producto');
            }
        })
        .catch(error => {
            console.error('Error al añadir producto:', error);
            showErrorNotification('Error en la conexión. Por favor, intenta más tarde.');
        });
    }
    
    // Función para actualizar contador del carrito
    function updateCartCount() {
        fetch(SITE_URL + '/api/cart/count.php')
            .then(response => response.json())
            .then(data => {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = data.count;
                    
                    // Actualizar también el localStorage para mantener consistencia
                    localStorage.setItem('cart_count', data.count.toString());
                }
            })
            .catch(error => console.error('Error al obtener conteo del carrito:', error));
    }
    
    // Funciones de notificación
    function showSuccessNotification(message, autoHide = true) {
        // Crear un modal en lugar de una notificación simple
        const modal = document.createElement('div');
        modal.className = 'user-data-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>¡Operación exitosa!</h3>
                    <span class="close-modal">&times;</span>
                </div>
                <div class="modal-body">
                    <div class="success-message-container">
                        <i class="fas fa-check-circle success-icon"></i>
                        <p>${message}</p>
                    </div>
                    <div class="confirmation-buttons">
                        <button id="confirm-action" class="btn btn-primary">Aceptar</button>
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
        
        // Manejar botón de aceptar
        const confirmBtn = modal.querySelector('#confirm-action');
        confirmBtn.addEventListener('click', function() {
            modal.remove();
        });
        
        // Auto-cerrar después de 3 segundos si se solicita
        if (autoHide) {
            setTimeout(() => {
                if (document.body.contains(modal)) {
                    modal.remove();
                }
            }, 3000);
        }
    }
    
    function showErrorNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'error-notification';
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-exclamation-circle"></i>
                <span>${message}</span>
            </div>
            <button class="close-notification">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        // Mostrar notificación con animación
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Cerrar al hacer clic en el botón
        const closeBtn = notification.querySelector('.close-notification');
        closeBtn.addEventListener('click', () => {
            hideNotification(notification);
        });
    }
    
    function hideNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
});