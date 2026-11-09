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

if (!isset($input['cart_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID del ítem no proporcionado']);
    exit;
}

$userId = $_SESSION['user_id'];
$cartId = (int)$input['cart_id'];

try {
    $conexion = connectDB();
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Obtener información del producto antes de eliminarlo
    $stmt = $conexion->prepare("SELECT c.cantidad, c.id_producto FROM carrito c WHERE c.id = ? AND c.id_usuario = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado en tu carrito']);
        exit;
    }
    
    $cartItem = $result->fetch_assoc();
    $cantidad = $cartItem['cantidad'];
    $productId = $cartItem['id_producto'];
    
    // Eliminar del carrito
    $stmt = $conexion->prepare("DELETE FROM carrito WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Devolver stock al producto
        $stmt = $conexion->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $cantidad, $productId);
        $stmt->execute();
        
        // Confirmar transacción
        $conexion->commit();
        
        echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
    } else {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el producto']);
    }
    
} catch (Exception $e) {
    if (isset($conexion)) {
        $conexion->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>