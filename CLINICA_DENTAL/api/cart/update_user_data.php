<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

header('Content-Type: application/json');

// Verificar si está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Comprobar que es una solicitud POST con JSON
$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['user_id'];

try {
    $connection = connectDB();
    
    // Obtener datos actuales del usuario
    $stmt = $connection->prepare("SELECT direccion, telefono FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();
    
    // Determinar qué campos actualizar
    $direccion = isset($input['direccion']) ? trim($input['direccion']) : $userData['direccion'];
    $telefono = isset($input['telefono']) ? trim($input['telefono']) : $userData['telefono'];
    
    // Validar solo los campos que se están enviando actualmente
    if (isset($input['direccion']) && empty($direccion)) {
        echo json_encode(['success' => false, 'message' => 'La dirección es obligatoria']);
        exit;
    }
    
    if (isset($input['telefono'])) {
        if (empty($telefono)) {
            echo json_encode(['success' => false, 'message' => 'El teléfono es obligatorio']);
            exit;
        }
        
        if (!preg_match('/^6[0-9]{8}$/', $telefono)) {
            echo json_encode(['success' => false, 'message' => 'El teléfono debe ser un móvil válido']);
            exit;
        }
    }
    
    // Actualizar los datos del usuario
    $stmt = $connection->prepare("UPDATE usuarios SET direccion = ?, telefono = ? WHERE id = ?");
    $stmt->bind_param("ssi", $direccion, $telefono, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0 || $stmt->affected_rows === 0) {
        echo json_encode(['success' => true, 'message' => 'Información actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la información']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($connection)) {
        closeDB($connection);
    }
}
?>