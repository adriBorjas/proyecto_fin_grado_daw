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
$fecha = $data['fecha'] ?? '';

if (empty($fecha)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'message' => 'Fecha no válida'
    ]);
    exit;
}

try {
    $conexion = connectDB();
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Actualizar todos los horarios específicos de esa fecha para que estén disponibles
    $stmt = $conexion->prepare("
        UPDATE horarios_especificos 
        SET disponible = 1 
        WHERE fecha = ? AND disponible = 0
    ");
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    
    $horariosActualizados = $stmt->affected_rows;
    $stmt->close();
    
    // Confirmar transacción
    $conexion->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Se han desbloqueado ' . $horariosActualizados . ' horarios para la fecha seleccionada.',
        'unblocked' => $horariosActualizados
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($conexion)) {
        $conexion->rollback();
    }
    
    error_log("Error al desbloquear horarios para la fecha $fecha: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al desbloquear horarios: ' . $e->getMessage()
    ]);
    
} finally {
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>