<?php
include_once '../../includes/header.php';
require_once '../../includes/db.php';
include_once '../../includes/check_admin.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-forms">
            <h2 class="form-title">Añadir Nuevo Usuario</h2>
            
            <?php if(isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['mensaje']; ?>
                    <?php unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['errores_admin']) && !empty($_SESSION['errores_admin'])): ?>
                <div class="error-container">
                    <?php foreach($_SESSION['errores_admin'] as $error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errores_admin']); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" id="admin-add-user-form" action="../../api/administrative-apis/add_user.php" method="post">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre de usuario" 
                           value="<?php echo isset($_SESSION['old_input_admin']['nombre']) ? html_entity_decode(htmlspecialchars($_SESSION['old_input_admin']['nombre'])) : ''; ?>" 
                           required>
                    <small class="input-help">Solo letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos. Máx. 16 caracteres.</small>
                    <span class="field-error" id="nombre-error"></span>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Correo electrónico" 
                           value="<?php echo isset($_SESSION['old_input_admin']['email']) ? htmlspecialchars($_SESSION['old_input_admin']['email']) : ''; ?>" 
                           required>
                    <span class="field-error" id="email-error"></span>
                </div>
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <small class="input-help">Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial</small>
                    <span class="field-error" id="password-error"></span>
                </div>
                <div class="form-group">
                    <input type="password" id="password2" name="password2" placeholder="Repetir contraseña" required>
                    <span class="field-error" id="password2-error"></span>
                </div>
                <div class="form-group">
                    <input type="text" id="direccion" name="direccion" placeholder="Dirección (opcional)"
                           value="<?php echo isset($_SESSION['old_input_admin']['direccion']) ? htmlspecialchars($_SESSION['old_input_admin']['direccion']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="tel" inputmode="numeric" id="telefono" name="telefono" maxlength="9" pattern="6[0-9]{8}" onkeypress="return isNumberKey(event)" placeholder="Teléfono móvil (opcional)"
                           value="<?php echo isset($_SESSION['old_input_admin']['telefono']) ? htmlspecialchars($_SESSION['old_input_admin']['telefono']) : ''; ?>">
                    <small class="input-help">Debe ser un móvil válido (9 dígitos que comience por 6)</small>
                    <span class="field-error" id="telefono-error"></span>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
            
            <?php 
            // Limpiar valores antiguos después de mostrarlos
            if(isset($_SESSION['old_input_admin'])) {
                unset($_SESSION['old_input_admin']);
            }
            ?>
            
            <!-- Botón de volver al panel de administración -->
            <div class="admin-back-link">
                <a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php" class="btn-back-admin">
                    <i class="fas fa-arrow-left"></i> Volver al panel de administración
                </a>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo SITE_URL; ?>/assets/js/utils.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/admin-user-validation.js?v=<?php echo time(); ?>"></script>
<?php include_once '../../includes/footer.php'; ?>
