<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// Verificar si está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasRequiredData' => false]);
    exit;
}

header('Content-Type: application/json');
$userId = $_SESSION['user_id'];

try {
    $connection = connectDB();
    
    // Verificar si el usuario tiene dirección y teléfono
    $stmt = $connection->prepare("SELECT direccion, telefono FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verificar si tiene los datos necesarios
        $hasDireccion = !empty($user['direccion']);
        $hasTelefono = !empty($user['telefono']) && preg_match('/^6[0-9]{8}$/', $user['telefono']);
        $hasRequiredData = $hasDireccion && $hasTelefono;
        
        // Registrar en el log los datos de dirección y teléfono
        error_log("Datos de usuario - ID: $userId, Dirección: " . ($hasDireccion ? 'Sí' : 'No') . ", Teléfono: " . ($hasTelefono ? 'Sí' : 'No'));
        
        // Devolver resultado con información detallada
        echo json_encode([
            'success' => true,
            'hasRequiredData' => $hasRequiredData,
            'hasDireccion' => $hasDireccion,
            'hasTelefono' => $hasTelefono
        ]);
    } else {
        echo json_encode(['hasRequiredData' => false]);
    }
    
} catch (Exception $e) {
    echo json_encode(['hasRequiredData' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($connection)) {
        closeDB($connection);
    }
}
?>