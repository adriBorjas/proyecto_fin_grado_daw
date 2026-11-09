<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/admin/manage_availability.php');
    exit;
}

// Obtener las fechas seleccionadas (vienen como string separado por comas)
$dates = trim($_POST['dates'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');

// Si el motivo es "Otro", usar el motivo especificado
if ($motivo === 'Otro') {
    $motivoPersonalizado = trim($_POST['otro_motivo'] ?? '');
    if (!empty($motivoPersonalizado)) {
        $motivo = $motivoPersonalizado;
    }
}

// Validaciones
$errores = [];

if (empty($dates)) {
    $errores[] = 'Debe seleccionar al menos un día para bloquear.';
}

// IMPORTANTE: Asegurar que siempre haya un motivo
if (empty($motivo)) {
    $motivo = "Día bloqueado por el administrador";
}

// Guardar el motivo original para depuración
$motivoOriginal = $motivo;
error_log("Motivo original de bloqueo: " . $motivoOriginal);

// Resto del código que utiliza el motivo para cancelar citas y crear notificaciones...

// Si hay errores, redirigir con mensaje de error
if (!empty($errores)) {
    $_SESSION['error'] = implode(' ', $errores);
    header('Location: ' . SITE_URL . '/pages/admin/manage_availability.php');
    exit;
}

// Procesar las fechas (convertir de string a array)
$dateArray = explode(',', $dates);
$conexion = connectDB();
$successCount = 0;
$errorCount = 0;
$citasCanceladas = 0;

// Iniciar transacción
$conexion->begin_transaction();

try {
    // Insertar cada fecha como un día no disponible
    foreach ($dateArray as $date) {
        $date = trim($date);
        
        if (empty($date)) continue;
        
        // Verificar si la fecha ya está bloqueada
        $checkStmt = $conexion->prepare("SELECT id FROM dias_no_disponibles WHERE fecha = ?");
        $checkStmt->bind_param("s", $date);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $errorCount++;
            $checkStmt->close();
            continue; // Esta fecha ya está bloqueada, continuar con la siguiente
        }
        
        $checkStmt->close();
        
        // NUEVO: Buscar todas las citas para esta fecha
        $citasStmt = $conexion->prepare("
            SELECT c.id, c.id_usuario, c.fecha, c.hora_inicio 
            FROM citas c 
            WHERE c.fecha = ? AND (c.estado = 'activa' OR c.estado IS NULL)
        ");
        $citasStmt->bind_param("s", $date);
        $citasStmt->execute();
        $citasResult = $citasStmt->get_result();
        
        // Cancelar todas las citas para esta fecha
        while ($cita = $citasResult->fetch_assoc()) {
            // Marcar la cita como cancelada
            $cancelStmt = $conexion->prepare("
                UPDATE citas 
                SET estado = 'cancelada', razon_cancelacion = ? 
                WHERE id = ?
            ");
            $cancelStmt->bind_param("si", $motivo, $cita['id']);
            
            if ($cancelStmt->execute()) {
                // Registrar la cita como cancelada para mostrar al usuario
                $citasCanceladas++;
                
                // Registrar en el log para depuración
                error_log("Cita ID: " . $cita['id'] . " cancelada automáticamente al bloquear el día " . $date . " - Motivo: " . $motivo);
                
                // Antes de insertar una notificación, verificar si ya existe
                $checkNotificationStmt = $conexion->prepare("
                    SELECT id FROM notificaciones_cancelaciones 
                    WHERE id_cita = ? AND id_usuario = ?
                ");
                $checkNotificationStmt->bind_param("ii", $cita['id'], $cita['id_usuario']);
                $checkNotificationStmt->execute();
                $notificationResult = $checkNotificationStmt->get_result();
                
                if ($notificationResult->num_rows == 0) {
                    // No hay notificación, crear una nueva
                    $notifyStmt = $conexion->prepare("
                        INSERT INTO notificaciones_cancelaciones (id_usuario, id_cita, fecha, hora, razon, leido)
                        VALUES (?, ?, ?, ?, ?, 0)
                    ");
                    $notifyStmt->bind_param("iisss", 
                        $cita['id_usuario'], 
                        $cita['id'], 
                        $cita['fecha'], 
                        $cita['hora_inicio'], 
                        $motivo
                    );
                    $notifyStmt->execute();
                    $notifyStmt->close();
                } else {
                    // Si ya existe, actualizar el motivo para priorizar el del admin
                    $updateNotifyStmt = $conexion->prepare("
                        UPDATE notificaciones_cancelaciones 
                        SET razon = ?, leido = 0 
                        WHERE id_cita = ? AND id_usuario = ?
                    ");
                    $updateNotifyStmt->bind_param("sii", $motivo, $cita['id'], $cita['id_usuario']);
                    $updateNotifyStmt->execute();
                    $updateNotifyStmt->close();
                }

                $checkNotificationStmt->close();
            }
            
            $cancelStmt->close();
        }
        
        $citasStmt->close();
        
        // Insertar la nueva fecha bloqueada
        $stmt = $conexion->prepare("INSERT INTO dias_no_disponibles (fecha, motivo) VALUES (?, ?)");
        $stmt->bind_param("ss", $date, $motivo);
        
        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
        }
        
        $stmt->close();
    }
    
    // Confirmar todas las operaciones
    $conexion->commit();
    
    // Mensaje de resultado
    if ($successCount > 0) {
        $_SESSION['mensaje'] = "Se han bloqueado $successCount día(s) correctamente.";
        if ($errorCount > 0) {
            $_SESSION['mensaje'] .= " No se pudieron bloquear $errorCount día(s) (posiblemente ya estaban bloqueados).";
        }
        
        if ($citasCanceladas > 0) {
            $_SESSION['mensaje'] .= " Se han cancelado automáticamente $citasCanceladas citas para los días bloqueados.";
        }
    } else {
        $_SESSION['error'] = "No se pudo bloquear ningún día. Por favor, verifica que las fechas no estén ya bloqueadas.";
    }
    
} catch (Exception $e) {
    // Si hay algún error, hacer rollback
    $conexion->rollback();
    
    error_log("Error al bloquear días: " . $e->getMessage());
    $_SESSION['error'] = "Error al bloquear los días: " . $e->getMessage();
    
} finally {
    closeDB($conexion);
    header('Location: ' . SITE_URL . '/pages/admin/manage_availability.php');
    exit;
}
?>