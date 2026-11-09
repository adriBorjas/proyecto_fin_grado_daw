document.addEventListener('DOMContentLoaded', function() {
    // Actualizar contador del carrito inmediatamente
    updateCartCount();

    // Definir la función global
    window.updateCartCount = function() {
        const SITE_URL = document.querySelector('meta[name="site-url"]').getAttribute('content');
        
        fetch(SITE_URL + '/api/cart/count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Obtener el elemento del contador
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        // Actualizar el número
                        cartCount.textContent = data.count || 0;
                        
                        // Mostrar u ocultar según si hay productos
                        if (data.count > 0) {
                            cartCount.style.display = 'flex';
                        } else {
                            cartCount.style.display = 'none';
                        }
                    }
                    
                    // Guardar en localStorage como respaldo
                    localStorage.setItem('cart_count', data.count || 0);
                }
            })
            .catch(error => {
                console.error('Error al actualizar contador del carrito:', error);
                
                // Usar localStorage como respaldo
                const storedCount = localStorage.getItem('cart_count');
                if (storedCount) {
                    const cartCount = document.getElementById('cart-count');
                    if (cartCount) {
                        cartCount.textContent = storedCount;
                        cartCount.style.display = parseInt(storedCount) > 0 ? 'flex' : 'none';
                    }
                }
            });
    };
});