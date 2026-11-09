<?php include_once '../includes/header.php'; ?>

<!-- Incluir el CSS específico para la página de error -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/error.css">

<section class="error-section">
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h2>Ha ocurrido un error</h2>
            
            <?php if(isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
                <p class="error-description"><?php echo $_SESSION['error_message']; ?></p>
                <?php unset($_SESSION['error_message']); ?>
                
                <?php if(isset($_SESSION['error_details']) && !empty($_SESSION['error_details'])): ?>
                    <div class="error-technical-details">
                        <p class="error-technical-title">Detalles técnicos:</p>
                        <div class="error-code"><?php echo $_SESSION['error_details']; ?></div>
                        <?php unset($_SESSION['error_details']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error_trace']) && !empty($_SESSION['error_trace'])): ?>
                    <div class="error-trace-container">
                        <button class="btn btn-toggle-trace" id="toggleTrace">Mostrar traza completa</button>
                        <div class="error-trace" id="errorTrace" style="display: none;">
                            <pre><?php echo $_SESSION['error_trace']; ?></pre>
                        </div>
                    </div>
                    <?php unset($_SESSION['error_trace']); ?>
                <?php endif; ?>
            <?php else: ?>
                <p class="error-description">Se ha producido un error inesperado. Por favor, inténtalo de nuevo más tarde.</p>
            <?php endif; ?>
            
            <div class="error-actions">
                <button class="btn btn-back" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i> Volver atrás
                </button>
                <a href="<?php echo SITE_URL; ?>/" class="btn btn-home">
                    <i class="fas fa-home"></i> Ir al inicio
                </a>
            </div>
        </div>
    </div>
</section>

<script>
// Script para mostrar/ocultar la traza de error
document.addEventListener('DOMContentLoaded', function() {
    const toggleButton = document.getElementById('toggleTrace');
    const traceContainer = document.getElementById('errorTrace');
    
    if (toggleButton && traceContainer) {
        toggleButton.addEventListener('click', function() {
            if (traceContainer.style.display === 'none') {
                traceContainer.style.display = 'block';
                toggleButton.textContent = 'Ocultar traza completa';
            } else {
                traceContainer.style.display = 'none';
                toggleButton.textContent = 'Mostrar traza completa';
            }
        });
    }
});
</script>

<?php include_once '../includes/footer.php'; ?>