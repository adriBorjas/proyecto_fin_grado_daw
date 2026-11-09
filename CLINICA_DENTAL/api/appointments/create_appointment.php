<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// A partir de aquí, podemos estar seguros de que el usuario está autenticado
// y existe en la base de datos, así que podemos continuar con la lógica de la API

// Obtener los datos del JSON recibido
$data = json_decode(file_get_contents('php://input'), true);
$date = $data['date'] ?? '';
$time = $data['time'] ?? '';
$userId = $_SESSION['user_id'];

// Validar formato de fecha y hora
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Formato de fecha u hora inválido'
    ]);
    exit;
}

try {
    $conexion = connectDB();
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Verificar que la fecha no esté bloqueada con FOR UPDATE para prevenir condiciones de carrera
    $stmt = $conexion->prepare("
        SELECT id FROM dias_no_disponibles 
        WHERE fecha = ? 
        FOR UPDATE
    ");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception("Esta fecha no está disponible para citas");
    }
    
    // Verificar que no exista ninguna cita para esta fecha y hora (activa o cancelada)
    // Al usar FOR UPDATE bloqueamos la fila hasta que terminemos la transacción
    $stmt = $conexion->prepare("
        SELECT id FROM citas 
        WHERE fecha = ? AND hora_inicio = ? AND (estado = 'activa' OR estado IS NULL)
        FOR UPDATE
    ");
    $stmt->bind_param("ss", $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception("Este horario ya no está disponible");
    }
    
    // Obtener la hora de fin
    $timeArray = explode(':', $time);
    $hour = (int)$timeArray[0];
    $endTime = ($hour + 1) . ':00:00'; // Una hora después
    
    // Crear la cita
    $stmt = $conexion->prepare("
        INSERT INTO citas (id_usuario, fecha, hora_inicio, hora_fin) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("isss", $userId, $date, $time, $endTime);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al crear la cita: " . $conexion->error);
    }
    
    // Confirmar los cambios
    $conexion->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Cita reservada correctamente'
    ]);
    
} catch (Exception $e) {
    // Deshacer cambios en caso de error
    if (isset($conexion)) {
        $conexion->rollback();
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
    // Registrar el error
    error_log('Error al crear cita: ' . $e->getMessage());
} finally {
    // Cerrar la conexión
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>