<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// Obtener los datos de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!isset($data['appointment_id']) || !isset($data['razon']) || 
    !in_array($data['razon'], ['Vacaciones', 'Enfermedad'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Faltan datos requeridos o razón inválida'
    ]);
    exit;
}

$appointmentId = $data['appointment_id'];
$razon = $data['razon'];

try {
    $conexion = connectDB();
    
    // Primero obtener información sobre la cita para registrar después
    $getStmt = $conexion->prepare("
        SELECT id_usuario, fecha, hora_inicio
        FROM citas
        WHERE id = ?
    ");
    $getStmt->bind_param('i', $appointmentId);
    $getStmt->execute();
    $result = $getStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('La cita no existe');
    }
    
    $citaInfo = $result->fetch_assoc();
    $userId = $citaInfo['id_usuario'];
    $getStmt->close();
    
    // Marcar la cita como cancelada
    $updateStmt = $conexion->prepare("
        UPDATE citas 
        SET estado = 'cancelada', razon_cancelacion = ?
        WHERE id = ?
    ");
    $updateStmt->bind_param('si', $razon, $appointmentId);
    $success = $updateStmt->execute();

    // Crear una notificación para que el usuario sepa que su cita fue cancelada
    if ($success) {
        // Solo crear notificación si no existe ya una para esta cita
        $checkExistingStmt = $conexion->prepare("
            SELECT id FROM notificaciones_cancelaciones 
            WHERE id_cita = ? AND id_usuario = ?
        ");
        $checkExistingStmt->bind_param("ii", $appointmentId, $userId);
        $checkExistingStmt->execute();
        $existingResult = $checkExistingStmt->get_result();
        
        if ($existingResult->num_rows === 0) {
            // Solo insertar si no existe notificación previa
            $notifyStmt = $conexion->prepare("
                INSERT INTO notificaciones_cancelaciones (id_usuario, id_cita, fecha, hora, razon, leido)
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $notifyStmt->bind_param("iisss", $userId, $appointmentId, $citaInfo['fecha'], $citaInfo['hora_inicio'], $razon);
            $notifyStmt->execute();
            $notifyStmt->close();
        }
        $checkExistingStmt->close();
            
        error_log("admin_cancel_appointment.php - Cita $appointmentId cancelada para usuario $userId con razón: $razon");
        echo json_encode([
            'success' => true,
            'message' => 'Cita cancelada correctamente'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al cancelar la cita: ' . $conexion->error
        ]);
    }
    
    $updateStmt->close();
    closeDB($conexion);
} catch (Exception $e) {
    http_response_code(500);
    error_log("admin_cancel_appointment.php - Exception: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al cancelar la cita: ' . $e->getMessage()
    ]);
}
?>