<?php
// Simplemente requerir la configuración sin verificaciones adicionales
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="site-url" content="<?php echo SITE_URL; ?>">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title>Clínica Dental</title>
    
    <!-- Favicons -->
    <link rel="icon" type="image/svg+xml" href="<?php echo SITE_URL; ?>/assets/img/logo/logo.svg?v=<?php echo time(); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo SITE_URL; ?>/assets/img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo SITE_URL; ?>/assets/img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo SITE_URL; ?>/assets/img/favicon/favicon-16x16.png">
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/img/favicon/favicon.ico">
    
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/modal.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-custom.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-availability.css?v=<?php echo time(); ?>">
    
    <script src="<?php echo SITE_URL; ?>/assets/js/utils.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/modal-manager.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/auth-alerts.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/cart-counter.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/notifications.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/auth-notifications.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/canceled-appointments.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/app-health-check.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/session-integrity.js?v=<?php echo time(); ?>"></script>
    
    <!-- Agregar Font Awesome para el icono de hamburguesa -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>/">
                    <img src="<?php echo SITE_URL; ?>/assets/img/logo/logo.svg" alt="Logo Clínica Dental">
                    <span class="logo-text">Clínica Dental</span>
                </a>
            </div>
            
            <!-- Botón de menú móvil -->
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <nav>
                <ul id="main-menu">
                    <li><a href="<?php echo SITE_URL; ?>/">Inicio</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/shop.php">Tienda</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/pages/cart.php" class="cart-link">
                                <i class="fas fa-shopping-cart"></i>
                                Carrito
                                <span id="cart-count" class="cart-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/pages/appointments.php" class="nav-link">
                                <i class="fas fa-calendar-check"></i> Reservar Cita
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="nav-link">
                                <i class="fas fa-user"></i> Mi Perfil
                            </a>
                        </li>
                        <?php if(isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == 0): ?>
                        <li><a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php">Administración</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo SITE_URL; ?>/api/authentication-apis/logout-user.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/pages/auth.php?action=login">Iniciar Sesión</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/pages/auth.php?action=register">Crear Cuenta</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
    
    <script>
    // Script para el menú móvil
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.mobile-menu-toggle');
        const mainMenu = document.getElementById('main-menu');
        
        menuToggle.addEventListener('click', function() {
            mainMenu.classList.toggle('active');
        });
        
        // Cerrar menú al hacer clic en un enlace
        const menuLinks = mainMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                mainMenu.classList.remove('active');
            });
        });
    });
    </script>
