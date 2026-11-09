<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

// Obtener la fecha específica
$fecha = $_GET['fecha'] ?? '';

// Validar el formato de la fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Formato de fecha no válido. Use YYYY-MM-DD.'
    ]);
    exit;
}

try {
    $conexion = connectDB();
    
    // Comprobar si hay horarios específicos para esta fecha
    $stmt = $conexion->prepare("
        SELECT hora_inicio, hora_fin, disponible 
        FROM horarios_especificos 
        WHERE fecha = ?
        ORDER BY hora_inicio
    ");
    $stmt->bind_param("s", $fecha);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $timeSlots = [];
    
    while ($row = $result->fetch_assoc()) {
        $timeSlots[] = [
            'hora_inicio' => $row['hora_inicio'],
            'hora_fin' => $row['hora_fin'],
            'disponible' => (bool)$row['disponible']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'timeSlots' => $timeSlots
    ]);
    
    $stmt->close();
    closeDB($conexion);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los horarios: ' . $e->getMessage()
    ]);
}
?>