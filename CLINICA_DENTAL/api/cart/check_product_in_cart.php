<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

header('Content-Type: application/json');

// Verificar si está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['exists' => false]);
    exit;
}

// Comprobar que es una solicitud POST con JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id'])) {
    echo json_encode(['exists' => false]);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = (int)$input['product_id'];

try {
    $conexion = connectDB();
    
    // Verificar si el producto ya está en el carrito
    $stmt = $conexion->prepare("SELECT id FROM carrito WHERE id_usuario = ? AND id_producto = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo json_encode(['exists' => ($result->num_rows > 0)]);
    
} catch (Exception $e) {
    echo json_encode(['exists' => false]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>