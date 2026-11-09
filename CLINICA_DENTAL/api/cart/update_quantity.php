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

if (!isset($input['cart_id']) || !isset($input['change'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos necesarios']);
    exit;
}

$userId = $_SESSION['user_id'];
$cartId = (int)$input['cart_id'];
$change = (int)$input['change'];

try {
    $conexion = connectDB();
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Obtener información actual del carrito
    $stmt = $conexion->prepare("SELECT id_producto, cantidad FROM carrito WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado en tu carrito']);
        exit;
    }
    
    $item = $result->fetch_assoc();
    $productId = $item['id_producto'];
    $currentQuantity = $item['cantidad'];
    $newQuantity = $currentQuantity + $change;
    
    // Si la cantidad es 0 o menos, eliminar el ítem
    if ($newQuantity <= 0) {
        // Eliminar del carrito
        $stmt = $conexion->prepare("DELETE FROM carrito WHERE id = ?");
        $stmt->bind_param("i", $cartId);
        $stmt->execute();
        
        // Devolver todo el stock
        $stmt = $conexion->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $currentQuantity, $productId);
        $stmt->execute();
        
        $conexion->commit();
        echo json_encode(['success' => true, 'message' => 'Producto eliminado del carrito']);
        exit;
    }
    
    // Verificar stock disponible actual del producto
    $stmt = $conexion->prepare("SELECT stock FROM productos WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    $stockDisponible = $product['stock'];
    
    // Si aumentamos la cantidad, verificar que hay stock suficiente
    if ($change > 0) {
        if ($stockDisponible < $change) {
            $conexion->rollback();
            echo json_encode([
                'success' => false, 
                'message' => 'Lo sentimos, solo hay ' . ($stockDisponible + $currentQuantity) . ' unidades disponibles de este producto.'
            ]);
            exit;
        }
        
        // Reducir stock
        $stmt = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $change, $productId);
        $stmt->execute();
        
    } else {
        // Si disminuimos la cantidad, devolver stock
        $stockADevolver = abs($change);
        $stmt = $conexion->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $stmt->bind_param("ii", $stockADevolver, $productId);
        $stmt->execute();
    }
    
    // Actualizar la cantidad en el carrito
    $stmt = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id = ?");
    $stmt->bind_param("ii", $newQuantity, $cartId);
    $stmt->execute();
    
    // Confirmar transacción
    $conexion->commit();
    
    echo json_encode(['success' => true, 'message' => 'Cantidad actualizada']);
    
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