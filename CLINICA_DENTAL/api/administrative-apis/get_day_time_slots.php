<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

// Obtener el día de la semana
$dia = intval($_GET['dia'] ?? 1);

// Validar el día (debe estar entre 1 y 7)
if ($dia < 1 || $dia > 7) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Día no válido. Debe ser un número entre 1 y 7.'
    ]);
    exit;
}

try {
    $conexion = connectDB();
    
    // Obtener los horarios disponibles para ese día
    $stmt = $conexion->prepare("
        SELECT hora_inicio, hora_fin, disponible
        FROM horarios_disponibles 
        WHERE dia_semana = ?
        ORDER BY hora_inicio
    ");
    $stmt->bind_param("i", $dia);
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