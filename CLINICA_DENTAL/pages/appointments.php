<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/pages/auth.php?action=login&require_login=true&redirect=appointments');
    exit;
}

include '../includes/header.php';
?>

<section class="appointments-section">
    <div class="container">
        <h1>Reserva tu cita</h1>
        <p class="section-description">Selecciona una fecha y horario disponible para reservar tu cita odontológica.</p>
        
        <div class="appointments-container">
            <div class="calendar-container">
                <h2>Selecciona una fecha</h2>
                <div id="appointments-calendar"></div>
            </div>
            
            <div class="time-slots-container">
                <h2>Horarios disponibles</h2>
                <p id="selected-date-display">Selecciona una fecha en el calendario</p>
                <div id="time-slots">
                    <!-- Quitamos el mensaje de carga -->
                    <div class="no-slots-message">Por favor, selecciona una fecha para ver los horarios disponibles.</div>
                </div>
            </div>
        </div>
        
        <div class="user-appointments">
            <h2>Mis citas</h2>
            <div id="user-appointments-list">
                <!-- Quitamos el mensaje de carga -->
            </div>
        </div>
    </div>
</section>

<!-- Modal para confirmar reserva (eliminando campo de motivo) -->
<div id="reserve-modal" class="modal" style="display:none">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar cita</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="appointment-form">
                <input type="hidden" id="appointment-date" name="date">
                <input type="hidden" id="appointment-time" name="time">
                
                <div class="confirmation-buttons">
                    <button type="button" class="btn btn-secondary" id="cancel-appointment">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="confirm-appointment">Confirmar cita</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/appointments.js?v=<?php echo time(); ?>"></script>

<script>
// Indicar si el usuario está logueado para uso en el JavaScript
const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
const SITE_URL = '<?php echo SITE_URL; ?>';
</script>

<?php include '../includes/footer.php'; ?>