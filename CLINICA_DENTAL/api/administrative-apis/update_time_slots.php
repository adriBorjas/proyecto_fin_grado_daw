<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/admin/manage_availability.php');
    exit;
}

// Obtener datos del formulario
$fechaEspecifica = $_POST['specific_date'] ?? '';
$horasInicio = $_POST['hora_inicio'] ?? [];
$horasFin = $_POST['hora_fin'] ?? [];
$slotStatus = $_POST['slot_status'] ?? [];
$motivoBloqueo = isset($_POST['motivo_bloqueo']) ? trim($_POST['motivo_bloqueo']) : '';

// Si el motivo es "Otro", usar el motivo especificado
if ($motivoBloqueo === 'Otro') {
    $motivoBloqueo = trim($_POST['otro_motivo_bloqueo'] ?? '');
}

// Asegurar que siempre haya un motivo
if (empty($motivoBloqueo)) {
    $motivoBloqueo = "Horario bloqueado por el administrador";
}

// Registrar el motivo para depuración
error_log("Motivo de bloqueo de horario: " . $motivoBloqueo);

// Validaciones
$errores = [];

if (empty($fechaEspecifica)) {
    $errores[] = 'Debe seleccionar una fecha.';
}

if (empty($horasInicio) || empty($horasFin)) {
    $errores[] = 'Debe especificar al menos un horario.';
}

if (count($horasInicio) !== count($horasFin) || count($horasInicio) !== count($slotStatus)) {
    $errores[] = 'Error en los datos enviados. Por favor, intente de nuevo.';
}

// Verificar si hay slots bloqueados y si se especificó un motivo
$haySlotsBloqueados = false;
foreach ($slotStatus as $status) {
    if ($status === 'blocked') {
        $haySlotsBloqueados = true;
        break;
    }
}

// Si hay slots bloqueados, requerir un motivo
if ($haySlotsBloqueados && empty($motivoBloqueo)) {
    $errores[] = 'Debe especificar un motivo para bloquear horarios.';
}

// Si hay errores, redirigir con mensaje de error
if (!empty($errores)) {
    $_SESSION['error'] = implode(' ', $errores);
    header('Location: ' . SITE_URL . '/pages/admin/manage_availability.php');
    exit;
}

try {
    $conexion = connectDB();
    
    // Comenzar transacción
    $conexion->begin_transaction();
    
    // Obtener citas afectadas para cancelarlas
    $citasCanceladas = 0;
    
    foreach ($horasInicio as $i => $horaInicio) {
        // Solo verificar citas para los slots que se están bloqueando
        if ($slotStatus[$i] === 'blocked') {
            $checkCitasStmt = $conexion->prepare("
                SELECT c.id, c.id_usuario, c.fecha, c.hora_inicio 
                FROM citas c 
                WHERE c.fecha = ? AND c.hora_inicio = ? AND (c.estado = 'activa' OR c.estado IS NULL)
            ");
            $checkCitasStmt->bind_param("ss", $fechaEspecifica, $horaInicio);
            $checkCitasStmt->execute();
            $citasResult = $checkCitasStmt->get_result();
            
            // Cancelar las citas afectadas
            while ($cita = $citasResult->fetch_assoc()) {
                // Marcar la cita como cancelada
                $cancelStmt = $conexion->prepare("
                    UPDATE citas 
                    SET estado = 'cancelada', razon_cancelacion = ? 
                    WHERE id = ?
                ");
                $cancelStmt->bind_param("si", $motivoBloqueo, $cita['id']);
                
                if ($cancelStmt->execute()) {
                    // Registrar la cita como cancelada para mostrar al usuario
                    $citasCanceladas++;
                    
                    // Registrar en el log para depuración
                    error_log("Cita ID: " . $cita['id'] . " cancelada automáticamente al bloquear el horario " . $horaInicio . " en la fecha " . $fechaEspecifica);
                    
                    // Antes de insertar una notificación, verificar si ya existe
                    $checkNotificationStmt = $conexion->prepare("
                        SELECT id FROM notificaciones_cancelaciones 
                        WHERE id_usuario = ? AND id_cita = ?
                    ");
                    $checkNotificationStmt->bind_param("ii", $cita['id_usuario'], $cita['id']);
                    $checkNotificationStmt->execute();
                    $notificationExists = $checkNotificationStmt->get_result()->num_rows > 0;
                    $checkNotificationStmt->close();
                    
                    // Solo insertar la notificación si no existe ya
                    if (!$notificationExists) {
                        $notifyStmt = $conexion->prepare("
                            INSERT INTO notificaciones_cancelaciones (id_usuario, id_cita, fecha, hora, razon, leido)
                            VALUES (?, ?, ?, ?, ?, 0)
                        ");
                        $notifyStmt->bind_param("iisss", $cita['id_usuario'], $cita['id'], $cita['fecha'], $cita['hora_inicio'], $motivoBloqueo);
                        $notifyStmt->execute();
                        $notifyStmt->close();
                    }
                }
                
                $cancelStmt->close();
            }
            
            $checkCitasStmt->close();
        }
    }
    
    // Eliminar horarios existentes para esta fecha específica
    $deleteStmt = $conexion->prepare("DELETE FROM horarios_especificos WHERE fecha = ?");
    $deleteStmt->bind_param("s", $fechaEspecifica);
    $deleteStmt->execute();
    $deleteStmt->close();
    
    // Insertar nuevos horarios
    $insertStmt = $conexion->prepare("INSERT INTO horarios_especificos (fecha, hora_inicio, hora_fin, disponible) VALUES (?, ?, ?, ?)");
    
    for ($i = 0; $i < count($horasInicio); $i++) {
        $horaInicio = $horasInicio[$i];
        $horaFin = $horasFin[$i];
        $disponible = ($slotStatus[$i] === 'available') ? 1 : 0;
        
        $insertStmt->bind_param("sssi", $fechaEspecifica, $horaInicio, $horaFin, $disponible);
        $insertStmt->execute();
    }
    
    $insertStmt->close();
    
    // Confirmar todas las operaciones
    $conexion->commit();
    
    // Mensaje de éxito
    $_SESSION['mensaje'] = "Horarios actualizados correctamente para la fecha " . date('d/m/Y', strtotime($fechaEspecifica)) . ".";
    
    // Añadir información sobre citas canceladas si las hay
    if ($citasCanceladas > 0) {
        $_SESSION['mensaje'] .= " Se han cancelado automáticamente $citasCanceladas cita(s) que estaban programadas en los horarios bloqueados.";
    }
    
} catch (Exception $e) {
    // Si hay algún error, hacer rollback
    $conexion->rollback();
    
    error_log("Error al actualizar horarios: " . $e->getMessage());
    $_SESSION['error'] = "Error al actualizar los horarios: " . $e->getMessage();
    
} finally {
    closeDB($conexion);
    header('Location: ' . SITE_URL . '/pages/admin/manage_availability.php');
    exit;
}
?>