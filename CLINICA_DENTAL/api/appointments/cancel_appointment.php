<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// Obtener los datos de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!isset($data['appointment_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Falta el ID de la cita'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];
$appointmentId = $data['appointment_id'];

try {
    $conexion = connectDB();
    
    // Verificar que la cita pertenece al usuario (sin verificar estado)
    $checkStmt = $conexion->prepare("
        SELECT id FROM citas
        WHERE id = ? AND id_usuario = ?
    ");
    $checkStmt->bind_param('ii', $appointmentId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No se puede cancelar esta cita'
        ]);
        $checkStmt->close();
        closeDB($conexion);
        exit;
    }
    
    $checkStmt->close();
    
    // Cancelar la cita (eliminarla en lugar de cambiar estado)
    $deleteStmt = $conexion->prepare("
        DELETE FROM citas
        WHERE id = ?
    ");
    $deleteStmt->bind_param('i', $appointmentId);
    $success = $deleteStmt->execute();
    
    if ($success) {
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
    
    $deleteStmt->close();
    closeDB($conexion);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al cancelar la cita: ' . $e->getMessage()
    ]);
}
?>