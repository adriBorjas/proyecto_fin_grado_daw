<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/error_handler.php';
require_once __DIR__ . '/../includes/check_authenticated_user.php';

function checkCanceledAppointments($userId) {
    $conexion = connectDB();
    
    // Modificar la consulta para evitar duplicados
    $stmt = $conexion->prepare("
        SELECT DISTINCT 
            c.id, 
            c.fecha, 
            c.hora_inicio,
            COALESCE(nc.razon, c.razon_cancelacion, 'Motivo no especificado') as razon_cancelacion
        FROM citas c
        LEFT JOIN notificaciones_cancelaciones nc 
            ON c.id = nc.id_cita AND c.id_usuario = nc.id_usuario AND nc.leido = 0
        WHERE c.id_usuario = ? 
        AND c.estado = 'cancelada' 
        AND (c.notificado = 0 OR nc.id IS NOT NULL)
        ORDER BY c.fecha DESC, c.hora_inicio DESC
    ");
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $canceledAppointments = [];
    
    while ($row = $result->fetch_assoc()) {
        $canceledAppointments[] = $row;
    }
    
    $stmt->close();
    closeDB($conexion);
    
    return $canceledAppointments;
}
?>