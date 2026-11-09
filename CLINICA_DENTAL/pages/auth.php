<?php include '../includes/header.php'; ?>
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/auth.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/auth-alerts.css">

<!-- Asegurarnos de incluir Font Awesome para los iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

<?php 
// Determinar qué formulario mostrar según el parámetro action
$action = isset($_GET['action']) ? $_GET['action'] : 'login';
$showLogin = ($action === 'login');
$showRegister = ($action === 'register');
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-tabs">
            <div class="auth-forms">
                <!-- Formulario de inicio de sesión -->
                <?php if ($showLogin): ?>
                <div id="login-form-container">
                    <h2 class="form-title">¡Inicia sesión ahora!</h2>
                    
                    <form id="login-form" class="auth-form" action="<?php echo SITE_URL; ?>/api/authentication-apis/login-user.php" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <input type="text" id="login-nombre" name="nombre" placeholder="Nombre de usuario" 
                                   value="<?php echo isset($_SESSION['old_input_login']['nombre']) ? html_entity_decode(htmlspecialchars($_SESSION['old_input_login']['nombre'])) : ''; ?>" 
                                   required>
                        </div>
                        <div class="form-group">
                            <input type="password" id="login-password" name="password" placeholder="Contraseña" required>
                        </div>
                        
                        <?php if(isset($_GET['redirect']) && !empty($_GET['redirect'])): ?>
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                        </div>
                    </form>
                    
                    <div class="auth-links">
                        <p>¿No tienes cuenta? 
                            <a href="<?php echo SITE_URL; ?>/pages/auth.php?action=register<?php echo isset($_GET['redirect']) ? '&redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>">Crear cuenta</a>
                        </p>
                    </div>
                    
                    <?php if(isset($_SESSION['errores_login']) && !empty($_SESSION['errores_login'])): ?>
                        <div class="error-container">
                            <?php foreach($_SESSION['errores_login'] as $error): ?>
                                <p class="error"><?php echo $error; ?></p>
                            <?php endforeach; ?>
                            <?php unset($_SESSION['errores_login']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php 
                // Limpiar valores antiguos después de mostrarlos
                if(isset($_SESSION['old_input_login'])) {
                    unset($_SESSION['old_input_login']);
                }
                ?>
                <?php endif; ?>
                
                <!-- Formulario de registro -->
                <?php if ($showRegister): ?>
                <div id="register-form-container">
                    <h2 class="form-title">¿No tienes cuenta? ¡Es muy fácil crearla!</h2>
                    
                    <?php if(isset($_SESSION['errores_registro']) && !empty($_SESSION['errores_registro'])): ?>
                        <div class="error-container">
                            <h4>Por favor corrige los siguientes errores:</h4>
                            <ul>
                                <?php foreach($_SESSION['errores_registro'] as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['errores_registro']); ?>
                    <?php endif; ?>
                    
                    <form action="<?php echo SITE_URL; ?>/api/authentication-apis/register-user.php" method="POST" class="auth-form" id="register-form">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <input type="text" id="reg-nombre" name="nombre" placeholder="Nombre de usuario" 
                                   value="<?php echo isset($_SESSION['old_input']['nombre']) ? html_entity_decode(htmlspecialchars($_SESSION['old_input']['nombre'])) : ''; ?>"
                                   required>
                            <small class="input-help">Solo letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos. Máx. 16 caracteres.</small>
                            <span class="field-error" id="reg-nombre-error"></span>
                        </div>
                        <div class="form-group">
                            <input type="email" id="reg-email" name="email" placeholder="Correo electrónico" 
                                   value="<?php echo isset($_SESSION['old_input']['email']) ? htmlspecialchars($_SESSION['old_input']['email']) : ''; ?>"
                                   required>
                            <span class="field-error" id="reg-email-error"></span>
                        </div>
                        <div class="form-group">
                            <input type="password" id="reg-password" name="password" placeholder="Contraseña" required>
                            <small class="input-help">Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial</small>
                            <span class="field-error" id="reg-password-error"></span>
                        </div>
                        <div class="form-group">
                            <input type="password" id="reg-password2" name="password2" placeholder="Repite la contraseña" required>
                            <span class="field-error" id="reg-password2-error"></span>
                        </div>
                        <div class="form-group">
                            <input type="text" id="reg-direccion" name="direccion" placeholder="Dirección (opcional)"
                                   value="<?php echo isset($_SESSION['old_input']['direccion']) ? html_entity_decode(htmlspecialchars($_SESSION['old_input']['direccion'])) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="tel" inputmode="numeric" id="reg-telefono" name="telefono" maxlength="9" placeholder="Teléfono móvil (opcional)"
                                   value="<?php echo isset($_SESSION['old_input']['telefono']) ? htmlspecialchars($_SESSION['old_input']['telefono']) : ''; ?>"
                                   onkeypress="return isNumberKey(event)">
                            <small class="input-help">Debe ser un móvil válido (9 dígitos que comience por 6)</small>
                            <span class="field-error" id="reg-telefono-error"></span>
                        </div>
                        
                        <!-- Si hay un parámetro de redirección, mantenerlo -->
                        <?php if(isset($_GET['redirect']) && !empty($_GET['redirect'])): ?>
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_GET['redirect']); ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Crear cuenta</button>
                        </div>
                    </form>
                    
                    <div class="auth-links">
                        <p>¿Ya tienes cuenta? 
                            <a href="<?php echo SITE_URL; ?>/pages/auth.php?action=login<?php echo isset($_GET['redirect']) ? '&redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>">Iniciar sesión</a>
                        </p>
                    </div>
                </div>
                <?php 
                // Limpiar valores antiguos después de mostrarlos
                if(isset($_SESSION['old_input'])) {
                    unset($_SESSION['old_input']);
                }
                ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Incluir script de notificaciones -->
<script src="<?php echo SITE_URL; ?>/assets/js/notifications.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/auth-alerts.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/login-required-notification.js"></script>

<?php include '../includes/footer.php'; ?>

<?php
// Para depuración: registrar los parámetros recibidos
if (isset($_GET['redirect'])) {
    error_log("Página auth.php - Parámetro redirect recibido: " . $_GET['redirect']);
}
?>
