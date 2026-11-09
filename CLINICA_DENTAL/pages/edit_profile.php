<?php
include '../includes/header.php';
require_once '../includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/pages/auth.php?action=login');
    exit;
}

// Obtener información del usuario
$userId = $_SESSION['user_id'];
$connection = connectDB();
$stmt = $connection->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el usuario
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    // No se encontró el usuario o hubo un problema con la consulta
    $_SESSION['error'] = 'No se pudo obtener la información del usuario. Por favor, inicie sesión de nuevo.';
    header('Location: ' . SITE_URL . '/pages/auth.php?action=login');
    exit;
}

// Cerrar la conexión y el statement
$stmt->close();
closeDB($connection);
?>

<section class="perfil-section">
    <div class="container">
        <h2>Modificar Datos</h2>

        <?php if(isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['mensaje']; ?>
                <?php unset($_SESSION['mensaje']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="edit-profile-container">
            <form id="edit-profile-form" class="auth-form" action="<?php echo SITE_URL; ?>/api/user/update_profile.php" method="post">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="nombre">Nombre de usuario:</label>
                    <input type="text" id="edit-nombre" name="nombre" value="<?php echo html_entity_decode(htmlspecialchars($user['nombre'])); ?>" required>
                    <small class="input-help">Solo letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos. Máx. 16 caracteres.</small>
                    <span class="field-error" id="edit-nombre-error"></span>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico:</label>
                    <input type="email" id="edit-email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <span class="field-error" id="edit-email-error"></span>
                </div>
                <div class="form-group">
                    <label for="password">Nueva contraseña (dejar en blanco para mantener la actual):</label>
                    <input type="password" id="edit-password" name="password">
                    <small class="input-help">Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial</small>
                    <span class="field-error" id="edit-password-error"></span>
                </div>
                <div class="form-group">
                    <label for="password2">Confirmar nueva contraseña:</label>
                    <input type="password" id="edit-password2" name="password2">
                    <span class="field-error" id="edit-password2-error"></span>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="edit-direccion" name="direccion" value="<?php echo html_entity_decode(htmlspecialchars($user['direccion'] ?? '')); ?>">
                    <span class="field-error" id="edit-direccion-error"></span>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" inputmode="numeric" id="edit-telefono" name="telefono" maxlength="9" pattern="6[0-9]{8}" onkeypress="return isNumberKey(event)" value="<?php echo htmlspecialchars($user['telefono'] ?? ''); ?>">
                    <small class="input-help">Debe ser un móvil válido (9 dígitos que comience por 6)</small>
                    <span class="field-error" id="edit-telefono-error"></span>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="<?php echo SITE_URL; ?>/pages/profile.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Scripts para validación -->
<script src="<?php echo SITE_URL; ?>/assets/js/edit-profile.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>
