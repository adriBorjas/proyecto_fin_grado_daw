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

// Obtener la fecha de la consulta
$date = $_GET['date'] ?? date('Y-m-d');

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Formato de fecha inválido'
    ]);
    exit;
}

try {
    $conexion = connectDB();
    
    // Verificar si la fecha está bloqueada completamente
    $blockStmt = $conexion->prepare("
        SELECT id FROM dias_no_disponibles
        WHERE fecha = ?
    ");
    $blockStmt->bind_param('s', $date);
    $blockStmt->execute();
    $blockResult = $blockStmt->get_result();
    
    if ($blockResult->num_rows > 0) {
        echo json_encode([
            'success' => true,
            'timeSlots' => [],
            'message' => 'Esta fecha no está disponible para citas'
        ]);
        $blockStmt->close();
        closeDB($conexion);
        exit;
    }
    $blockStmt->close();
    
    // Obtener el día de la semana (1-7, donde 1 es lunes)
    $dayOfWeek = date('N', strtotime($date));
    
    // NUEVO: Primero comprobar si hay horarios específicos para esta fecha
    $specificStmt = $conexion->prepare("
        SELECT hora_inicio, hora_fin, disponible
        FROM horarios_especificos
        WHERE fecha = ?
        ORDER BY hora_inicio
    ");
    $specificStmt->bind_param('s', $date);
    $specificStmt->execute();
    $specificResult = $specificStmt->get_result();
    
    $timeSlots = [];
    
    // Si hay horarios específicos para esta fecha, utilizamos esos
    if ($specificResult->num_rows > 0) {
        while ($row = $specificResult->fetch_assoc()) {
            // Si el horario específico está marcado como no disponible, mostrarlo como no disponible
            if (!$row['disponible']) {
                $timeSlots[] = [
                    'hora_inicio' => $row['hora_inicio'],
                    'hora_fin' => $row['hora_fin'],
                    'available' => false
                ];
                continue;
            }
            
            // Verificar si este horario ya está reservado
            $checkStmt = $conexion->prepare("
                SELECT id FROM citas
                WHERE fecha = ? AND hora_inicio = ? AND (estado = 'activa' OR estado IS NULL)
            ");
            $checkStmt->bind_param('ss', $date, $row['hora_inicio']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            // Agregar el slot al array, pero marcándolo como disponible o no según corresponda
            $timeSlots[] = [
                'hora_inicio' => $row['hora_inicio'],
                'hora_fin' => $row['hora_fin'],
                'available' => ($checkResult->num_rows === 0)
            ];
            
            $checkStmt->close();
        }
    } else {
        // Si no hay horarios específicos, usamos los horarios predeterminados por día de la semana
        $stmt = $conexion->prepare("
            SELECT hd.hora_inicio, hd.hora_fin 
            FROM horarios_disponibles hd
            WHERE hd.dia_semana = ? AND hd.disponible = 1
            ORDER BY hd.hora_inicio
        ");
        $stmt->bind_param('i', $dayOfWeek);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Verificar si este horario ya está reservado
            $checkStmt = $conexion->prepare("
                SELECT id FROM citas
                WHERE fecha = ? AND hora_inicio = ? AND (estado = 'activa' OR estado IS NULL)
            ");
            $checkStmt->bind_param('ss', $date, $row['hora_inicio']);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            // Agregar el slot al array, pero marcándolo como disponible o no según corresponda
            $timeSlots[] = [
                'hora_inicio' => $row['hora_inicio'],
                'hora_fin' => $row['hora_fin'],
                'available' => ($checkResult->num_rows === 0)
            ];
            
            $checkStmt->close();
        }
        
        $stmt->close();
    }
    
    $specificStmt->close();
    
    echo json_encode([
        'success' => true,
        'timeSlots' => $timeSlots
    ]);
    
    closeDB($conexion);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener los horarios: ' . $e->getMessage()
    ]);
}
?>