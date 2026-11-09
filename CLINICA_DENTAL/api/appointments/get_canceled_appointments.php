<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// Iniciar o continuar la sesión si es necesario
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Log para depuración
error_log("get_canceled_appointments.php - Inicio de la solicitud");

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    error_log("get_canceled_appointments.php - Usuario no autorizado");
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// Verificar si hay citas canceladas en la sesión
if (isset($_SESSION['canceled_appointments']) && !empty($_SESSION['canceled_appointments'])) {
    $canceledAppointments = $_SESSION['canceled_appointments'];
    error_log("get_canceled_appointments.php - Encontradas " . count($canceledAppointments) . " citas canceladas en sesión");
    
    // Limpiar la sesión
    unset($_SESSION['canceled_appointments']);
    
    echo json_encode([
        'success' => true,
        'appointments' => $canceledAppointments
    ]);
} else {
    error_log("get_canceled_appointments.php - No se encontraron citas canceladas en la sesión, verificando en BD");
    
    // Verificar directamente en la base de datos si hay citas canceladas
    $userId = $_SESSION['user_id'];
    $conexion = connectDB();
    
    try {
        // Modificar la consulta para asegurar que se obtenga el motivo correctamente
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
            
            // Marcar la cita como notificada
            $updateStmt = $conexion->prepare("
                UPDATE citas 
                SET notificado = 1 
                WHERE id = ? AND id_usuario = ?
            ");
            $updateStmt->bind_param("ii", $row['id'], $userId);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Marcar la notificación como leída SI EXISTE
            $updateNotifyStmt = $conexion->prepare("
                UPDATE notificaciones_cancelaciones 
                SET leido = 1 
                WHERE id_cita = ? AND id_usuario = ?
            ");
            $updateNotifyStmt->bind_param("ii", $row['id'], $userId);
            $updateNotifyStmt->execute();
            $updateNotifyStmt->close();
        }
        
        // Añadir log aquí
        error_log("get_canceled_appointments.php - Citas canceladas recuperadas: " . json_encode($canceledAppointments));
        
        echo json_encode([
            'success' => true,
            'appointments' => $canceledAppointments
        ]);
        
        $stmt->close();
        closeDB($conexion);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener citas canceladas: ' . $e->getMessage()
        ]);
    }
}
?>