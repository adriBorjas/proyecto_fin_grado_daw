<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => 'ID de día no válido'
    ]);
    exit;
}

try {
    $conexion = connectDB();
    
    // Eliminar el día bloqueado
    $stmt = $conexion->prepare("DELETE FROM dias_no_disponibles WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    
    if ($result && $stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Día desbloqueado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo desbloquear el día. Posiblemente ya fue desbloqueado.'
        ]);
    }
    
    $stmt->close();
    closeDB($conexion);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
}
?>