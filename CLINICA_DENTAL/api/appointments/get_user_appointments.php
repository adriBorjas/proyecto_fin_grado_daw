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

$userId = $_SESSION['user_id'];

try {
    $conexion = connectDB();
    
    // Modificar la consulta para obtener SOLO las citas ACTIVAS
    $stmt = $conexion->prepare("
        SELECT id, fecha, hora_inicio, hora_fin
        FROM citas
        WHERE id_usuario = ? AND (estado = 'activa' OR estado IS NULL)
        ORDER BY fecha, hora_inicio
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'appointments' => $appointments
    ]);
    
    $stmt->close();
    closeDB($conexion);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener las citas: ' . $e->getMessage()
    ]);
}
?>