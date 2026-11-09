/**
 * Utilidades comunes para toda la aplicación
 */

// Función para permitir solo números en un campo de entrada
function isNumberKey(evt) {
    const charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

// Hacer global isNumberKey
window.isNumberKey = isNumberKey;

// Añadir automáticamente el token CSRF a las peticiones que modifican datos.
const originalFetch = window.fetch.bind(window);
window.fetch = function(input, init = {}) {
    const method = (init.method || 'GET').toUpperCase();
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    if (csrfMeta && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
        const headers = new Headers(init.headers || {});
        headers.set('X-CSRF-Token', csrfMeta.getAttribute('content'));
        init = { ...init, headers };
    }

    return originalFetch(input, init);
};

// Función para validar teléfono (9 dígitos)
function validateTelefono(telefono) {
    const regex = /^6[0-9]{8}$/;
    return regex.test(telefono);
}

// Función para validar nombre de usuario
function validateNombre(nombre) {
    // Solo letras sin acentos, números, guiones y guiones bajos, máximo 16 caracteres
    const regex = /^[a-zA-Z0-9_-]{1,16}$/;
    return regex.test(nombre);
}

// Función para validar email
function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Función para validar contraseña
function validatePassword(password) {
    // Al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial
    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/;
    return regex.test(password);
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
