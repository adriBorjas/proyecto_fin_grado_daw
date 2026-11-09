</main>
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="<?php echo SITE_URL; ?>/assets/img/logo/logo.svg?v=<?php echo time(); ?>" alt="Clínica Dental" class="footer-logo-img">
                    <h3>Clínica Dental</h3>
                    <p>Tu sonrisa, nuestra prioridad</p>
                </div>
                <div class="footer-contact">
                    <h4>Contáctanos</h4>
                    <p>Calle Mediterráneo, 8</p>
                    <p>28001, Madrid</p>
                    <p>info@clinicadental.com</p>
                    <div class="social-icons">
                        <a href="https://instagram.com/clinicadental" target="_blank" title="Síguenos en Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://facebook.com/clinicadental" target="_blank" title="Síguenos en Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://wa.me/34600000000" target="_blank" title="Contáctanos por WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="tel:+34600000000" title="Llámanos">
                            <i class="fas fa-phone-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Clínica Dental. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Incluir Font Awesome para los iconos (versión actualizada) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts adicionales -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    <script>
// Función para actualizar el contador del carrito en TODAS las páginas
function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (!cartCountElement) return;
    
    fetch('<?php echo SITE_URL; ?>/api/cart/get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            // Mostrar siempre el contador con el número exacto de elementos
            cartCountElement.textContent = data.count;
            cartCountElement.style.display = 'flex';
            cartCountElement.dataset.visible = 'true';
            
            // Guardar en localStorage como respaldo
            localStorage.setItem('cart_count', data.count);
        })
        .catch(error => {
            console.error('Error al obtener conteo del carrito:', error);
            
            // En caso de error, intentar obtener el valor desde localStorage
            const storedCount = localStorage.getItem('cart_count') || '0';
            cartCountElement.textContent = storedCount;
            cartCountElement.style.display = 'flex';
            cartCountElement.dataset.visible = 'true';
        });
}

// Llamar a la función al cargar la página
document.addEventListener('DOMContentLoaded', updateCartCount);

// Actualizar el contador cada 30 segundos para mantenerlo sincronizado
setInterval(updateCartCount, 30000);
</script>
</body>
</html>