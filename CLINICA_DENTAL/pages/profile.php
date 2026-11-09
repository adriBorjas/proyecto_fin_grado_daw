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

// Intentar recuperar los datos del usuario
$stmt = $connection->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $isAdmin = isset($user['rol']) && $user['rol'] == 0;
} else {
    // Error crítico: ID de usuario en sesión no existe en la base de datos
    error_log("ERROR CRÍTICO: Usuario ID $userId no existe en la base de datos pero está en sesión");
    
    // Recrear la sesión
    session_regenerate_id(true);
    
    // Informar al usuario
    $_SESSION['error'] = 'Hubo un problema con tu sesión. Por favor, inicia sesión nuevamente.';
    
    // Redirigir al login
    header('Location: ' . SITE_URL . '/pages/auth.php?action=login&session_error=true');
    exit;
}

// Cerrar el statement y la conexión
$stmt->close();
closeDB($connection);
?>

<section class="perfil-section">
    <div class="container">
        <h2>Mi Perfil</h2>

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
        
        <!-- Mostrar información personal -->
        <div class="user-profile">
            <div class="profile-info">
                <h3>Información personal</h3>
                <?php if (isset($user) && !empty($user)): ?>
                <div class="info-item">
                    <span class="label">Nombre de usuario:</span>
                    <span class="value"><?php echo htmlspecialchars($user['nombre']); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Correo electrónico:</span>
                    <span class="value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <?php if(!empty($user['direccion'])): ?>
                <div class="info-item">
                    <span class="label">Dirección:</span>
                    <span class="value"><?php echo htmlspecialchars($user['direccion']); ?></span>
                </div>
                <?php endif; ?>
                <?php if(!empty($user['telefono'])): ?>
                <div class="info-item">
                    <span class="label">Teléfono:</span>
                    <span class="value"><?php echo htmlspecialchars($user['telefono']); ?></span>
                </div>
                <?php endif; ?>
                <?php if(isset($user['rol'])): ?>
                <div class="info-item">
                    <span class="label">Rol:</span>
                    <span class="value"><?php echo ($user['rol'] == 0) ? 'Administrador' : 'Usuario'; ?></span>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="alert alert-warning">No se pudo cargar la información del usuario.</div>
                <?php endif; ?>
            </div>
            
            <div class="user-actions">
                <h3>Acciones</h3>
                <a href="<?php echo SITE_URL; ?>/api/authentication-apis/logout-user.php" class="btn btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Cerrar sesión</span>
                </a>
                
                <!-- Botón para reservar citas -->
                <a href="<?php echo SITE_URL; ?>/pages/appointments.php" class="btn btn-appointment-profile">
                    <i class="far fa-calendar-plus"></i>
                    <span>Reservar cita</span>
                </a>
                
                <!-- Botón para modificar datos -->
                <a href="<?php echo SITE_URL; ?>/pages/edit_profile.php" class="btn btn-edit-profile">
                    <i class="fas fa-user-edit"></i>
                    <span>Modificar datos</span>
                </a>

                <!-- Botón para ir a la tienda (visible para todos) -->
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-shop-profile">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Ir a la tienda</span>
                </a>

                <!-- Botón para ir a administración (solo para administradores) -->
                <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] == 0): ?>
                <a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php" class="btn btn-admin-profile">
                    <i class="fas fa-cogs"></i>
                    <span>Administración</span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
        <div class="user-appointments">
            <h2>Mis próximas citas</h2>
            <div id="profile-appointments-list" class="profile-appointments">
                <!-- Las citas se cargarán dinámicamente con JavaScript -->
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Incluir scripts JavaScript necesarios -->
<script>
// Variable global para uso en los scripts
const SITE_URL = '<?php echo SITE_URL; ?>';

// Verificar si debemos mostrar notificaciones de citas canceladas
const shouldShowNotifications = <?php echo isset($_GET['show_canceled']) && $_GET['show_canceled'] === '1' ? 'true' : 'false'; ?>;
console.log('¿Debería mostrar notificaciones?', shouldShowNotifications);

// Para depuración: enviar parámetro a la consola
document.addEventListener('DOMContentLoaded', function() {
    if (shouldShowNotifications) {
        console.log('Se debe mostrar el modal de notificaciones de citas canceladas');
    }
});
</script>

<script src="<?php echo SITE_URL; ?>/assets/js/profile.js?v=<?php echo time(); ?>"></script>
<!-- Asegurar que este script se carga siempre -->
<script src="<?php echo SITE_URL; ?>/assets/js/canceled-appointments.js?v=<?php echo time(); ?>"></script>
<?php include '../includes/footer.php'; ?>