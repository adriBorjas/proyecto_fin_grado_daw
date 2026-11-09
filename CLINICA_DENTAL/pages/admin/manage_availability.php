<?php
include_once '../../includes/header.php';
require_once '../../includes/db.php';
include_once '../../includes/check_admin.php';

// Asegurar que tenemos una conexión
$conexion = connectDB();
?>

<!-- Asegurar que se cargan todos los estilos necesarios -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-dashboard.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-custom.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-availability.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<section class="admin-section">
    <div class="container">
        <h2>Gestión de Disponibilidad</h2>

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
        
        <div class="availability-panel">
            <div class="availability-cards">
                <!-- Bloquear días completos - Primera fila, primera columna -->
                <div class="availability-card wider-card">
                    <h4>Bloquear Días Completos</h4>
                    <p>Selecciona los días que deseas bloquear completamente para citas</p>
                    
                    <form id="block-days-form" action="<?php echo SITE_URL; ?>/api/administrative-apis/block_days.php" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label for="block-days">Seleccionar días:</label>
                            <input type="text" id="block-days" name="dates" class="date-picker" placeholder="Selecciona fechas" required>
                        </div>
                        <div class="form-group">
                            <label for="block-reason">Motivo:</label>
                            <select id="block-reason" name="motivo" required>
                                <option value="" disabled selected>Selecciona un motivo</option>
                                <option value="Vacaciones">Vacaciones</option>
                                <option value="Formación">Formación Profesional</option>
                                <option value="Mantenimiento">Mantenimiento Clínica</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <!-- Modificar el campo de otro motivo para bloquear días -->
                        <div class="form-group" id="other-reason-group" style="display:none;">
                            <label for="other-reason">Especificar motivo:</label>
                            <input type="text" id="other-reason" name="otro_motivo" 
                                   placeholder="Indica el motivo" 
                                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
                                   title="Solo se permiten letras y espacios" 
                                   required>
                        </div>
                        <div class="form-action-container">
                            <button type="submit" class="btn-action-primary">
                                <i class="fas fa-calendar-check"></i> Guardar Bloqueo
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Gestionar horarios por día - Primera fila, segunda columna -->
                <div class="availability-card wider-card">
                    <h4>Gestionar Horarios por Día</h4>
                    <p>Configura los horarios disponibles para cada día de la semana</p>
                    
                    <!-- Reemplazar la sección del formulario de horarios por día -->
                    <form id="time-slots-form" action="<?php echo SITE_URL; ?>/api/administrative-apis/update_time_slots.php" method="post">
                        <?php echo csrf_field(); ?>
                        <div class="form-group">
                            <label for="specific-date">Seleccionar fecha:</label>
                            <input type="text" id="specific-date" name="specific_date" class="date-picker" placeholder="Selecciona una fecha" required>
                            <input type="hidden" id="day-from-date" name="dia_semana" value="">
                        </div>
                        
                        <!-- Reemplazar solo la sección del selector de motivo de bloqueo -->
                        <div class="form-group">
                            <label for="block-reason-slot">Motivo de bloqueo:</label>
                            <select id="block-reason-slot" name="motivo_bloqueo" required>
                                <option value="" disabled selected>Selecciona un motivo</option>
                                <option value="Vacaciones">Vacaciones</option>
                                <option value="Formación">Formación Profesional</option>
                                <option value="Mantenimiento">Mantenimiento Clínica</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <!-- Modificar el campo de otro motivo para bloquear horarios -->
                        <div class="form-group" id="other-reason-slot-group" style="display:none;">
                            <label for="other-reason-slot">Especificar motivo:</label>
                            <input type="text" id="other-reason-slot" name="otro_motivo_bloqueo" 
                                   placeholder="Indica el motivo" 
                                   pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
                                   title="Solo se permiten letras y espacios" 
                                   required>
                        </div>
                        
                        <div id="time-slots-container">
                            <!-- Los slots de horario se cargarán dinámicamente con JavaScript -->
                        </div>
                        
                        <div class="form-action-container">
                            <button type="submit" class="btn-action-primary">
                                <i class="fas fa-clock"></i> Actualizar Horarios
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Ver días bloqueados - Segunda fila, columna completa -->
                <div class="availability-card full-width-card">
                    <h4>Días Bloqueados</h4>
                    <p>Gestiona los días que están bloqueados actualmente</p>
                    
                    <div class="blocked-days">
                        <?php
                        try {
                            $stmt = $conexion->prepare("
                                SELECT id, fecha, motivo 
                                FROM dias_no_disponibles 
                                WHERE fecha >= CURDATE()
                                ORDER BY fecha ASC
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                echo '<div class="blocked-days-table-container">'; // Añadir este div contenedor
                                echo '<table class="blocked-days-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Motivo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                    
                                while ($day = $result->fetch_assoc()) {
                                    $formattedDate = date('d/m/Y', strtotime($day['fecha']));
                                    echo '<tr>';
                                    echo '<td>' . $formattedDate . '</td>';
                                    echo '<td>' . htmlspecialchars($day['motivo']) . '</td>';
                                    echo '<td>
                                        <button class="btn-unblock-day" data-id="' . $day['id'] . '">
                                            <i class="fas fa-unlock"></i> Desbloquear
                                        </button>
                                    </td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody></table>';
                                echo '</div>'; // Cerrar el div contenedor
                            } else {
                                echo '<p>No hay días bloqueados próximamente.</p>';
                            }
                        } catch (Exception $e) {
                            echo '<p class="error-message">Error al cargar los días bloqueados: ' . $e->getMessage() . '</p>';
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Nueva sección para visualizar horarios bloqueados -->
                <div class="availability-card full-width-card">
                    <h4>Horarios Bloqueados</h4>
                    <p>Visualiza los horarios específicos que han sido bloqueados por fecha</p>
                    
                    <div class="blocked-hours-container">
                        <?php
                        try {
                            // Consultar horarios específicos bloqueados agrupados por fecha
                            $stmt = $conexion->prepare("
                                SELECT fecha, 
                                       GROUP_CONCAT(CONCAT(TIME_FORMAT(hora_inicio, '%H:%i'), ' - ', TIME_FORMAT(hora_fin, '%H:%i')) SEPARATOR ', ') as horarios,
                                       COUNT(*) as total
                                FROM horarios_especificos
                                WHERE disponible = 0 AND fecha >= CURDATE()
                                GROUP BY fecha
                                ORDER BY fecha ASC
                                LIMIT 20
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                echo '<div class="blocked-hours-table-container">';
                                echo '<table class="blocked-hours-table">';
                                echo '<thead>';
                                echo '<tr>';
                                echo '<th>Fecha</th>';
                                echo '<th>Horarios Bloqueados</th>';
                                echo '<th>Total</th>';
                                echo '<th>Acciones</th>'; // Nueva columna para acciones
                                echo '</tr>';
                                echo '</thead>';
                                echo '<tbody>';
                                
                                while ($row = $result->fetch_assoc()) {
                                    $formattedDate = date('d/m/Y', strtotime($row['fecha']));
                                    echo '<tr>';
                                    echo '<td>' . $formattedDate . '</td>';
                                    echo '<td class="blocked-hours-list">' . $row['horarios'] . '</td>';
                                    echo '<td class="blocked-hours-count">' . $row['total'] . '</td>';
                                    echo '<td>
                                        <button class="btn-unblock-day-hours" data-fecha="' . $row['fecha'] . '">
                                            <i class="fas fa-unlock"></i> Desbloquear todos
                                        </button>
                                    </td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                                echo '</div>';
                            } else {
                                echo '<p class="no-blocked-hours">No hay horarios específicos bloqueados próximamente.</p>';
                            }
                            
                            $stmt->close();
                        } catch (Exception $e) {
                            echo '<p class="error-message">Error al cargar los horarios bloqueados: ' . $e->getMessage() . '</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
            <!-- Botón de volver al panel de administración -->
            <div class="admin-back-link">
                <a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php" class="btn-back-admin">
                    <i class="fas fa-arrow-left"></i> Volver al panel de administración
                </a>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    const SITE_URL = '<?php echo SITE_URL; ?>';
</script>
<script src="<?php echo SITE_URL; ?>/assets/js/admin-availability.js?v=<?php echo time(); ?>"></script>
<?php 
// Cerrar la conexión solo al finalizar el archivo
closeDB($conexion);
include_once '../../includes/footer.php'; 
?>
