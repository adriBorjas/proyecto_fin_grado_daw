<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// Obtener datos de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

if (!isset($data['appointment_ids']) || !is_array($data['appointment_ids'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos inválidos'
    ]);
    exit;
}

$appointmentIds = $data['appointment_ids'];

try {
    $conexion = connectDB();
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // MODIFICADO: En lugar de marcar como notificada, ELIMINAR las citas canceladas
    $citas_stmt = $conexion->prepare("
        DELETE FROM citas 
        WHERE id = ? AND id_usuario = ? AND estado = 'cancelada'
    ");
    
    // ELIMINAR las notificaciones de cancelaciones
    $notif_stmt = $conexion->prepare("
        DELETE FROM notificaciones_cancelaciones 
        WHERE id_cita = ? AND id_usuario = ?
    ");
    
    $rows_affected = 0;
    
    foreach ($appointmentIds as $appointmentId) {
        // Eliminar citas
        $citas_stmt->bind_param("ii", $appointmentId, $userId);
        $citas_stmt->execute();
        $rows_affected += $citas_stmt->affected_rows;
        
        // Eliminar notificaciones
        $notif_stmt->bind_param("ii", $appointmentId, $userId);
        $notif_stmt->execute();
        $rows_affected += $notif_stmt->affected_rows;
    }
    
    // Confirmar transacción
    $conexion->commit();
    
    // Log para depuración
    error_log("Eliminadas $rows_affected filas (citas canceladas y notificaciones) para el usuario $userId");
    
    // Devolver respuesta
    echo json_encode([
        'success' => true,
        'rows_affected' => $rows_affected,
        'message' => 'Citas eliminadas correctamente'
    ]);
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    if (isset($conexion)) {
        $conexion->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    
    // Log del error
    error_log("Error al eliminar citas canceladas: " . $e->getMessage());
    
} finally {
    // Cerrar statements y conexión
    if (isset($citas_stmt)) $citas_stmt->close();
    if (isset($notif_stmt)) $notif_stmt->close();
    if (isset($conexion)) closeDB($conexion);
}
?>