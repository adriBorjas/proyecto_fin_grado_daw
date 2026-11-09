<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// Verificar si está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// Comprobar que es una solicitud POST con JSON
$input = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos necesarios']);
    exit;
}

$userId = $_SESSION['user_id'];
$productId = (int)$input['product_id'];
$quantity = (int)$input['quantity'];
$isPending = isset($input['is_pending']) && $input['is_pending'] === true;

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'La cantidad debe ser mayor a 0']);
    exit;
}

try {
    $conexion = connectDB();
    
    // Iniciar transacción para garantizar consistencia
    $conexion->begin_transaction();
    
    // Verificar si el producto existe y tiene stock
    $stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => 'Producto no disponible']);
        exit;
    }
    
    $producto = $resultado->fetch_assoc();
    
    // Verificar si el producto ya está en el carrito
    $stmt = $conexion->prepare("SELECT c.id, c.cantidad, p.stock FROM carrito c 
                               JOIN productos p ON c.id_producto = p.id 
                               WHERE c.id_usuario = ? AND c.id_producto = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        // El producto ya está en el carrito, actualizar cantidad
        $item = $resultado->fetch_assoc();
        $newQuantity = $item['cantidad'] + $quantity;
        
        // Validar que no exceda el stock disponible
        if ($newQuantity > $producto['stock']) {
            $conexion->rollback();
            if ($isPending) {
                $newQuantity = $producto['stock'];
                if ($newQuantity <= $item['cantidad']) {
                    echo json_encode(['success' => false, 'message' => 'Producto ya añadido con el máximo stock disponible.']);
                    exit;
                }
                $quantity = $newQuantity - $item['cantidad'];
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Lo sentimos, solo hay ' . $producto['stock'] . ' unidades disponibles de este producto.'
                ]);
                exit;
            }
        }
        
        // Actualizar carrito
        $stmt = $conexion->prepare("UPDATE carrito SET cantidad = ? WHERE id_usuario = ? AND id_producto = ?");
        $stmt->bind_param("iii", $newQuantity, $userId, $productId);
        $stmt->execute();
        
        // Reducir stock disponible
        $stmt = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $productId);
        $stmt->execute();
        
    } else {
        // Verificar que hay suficiente stock para añadir
        if ($quantity > $producto['stock']) {
            $conexion->rollback();
            if ($isPending) {
                $quantity = $producto['stock'];
                if ($quantity <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Lo sentimos, este producto está agotado.']);
                    exit;
                }
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Lo sentimos, solo hay ' . $producto['stock'] . ' unidades disponibles de este producto.'
                ]);
                exit;
            }
        }
        
        // Añadir nuevo item al carrito
        $stmt = $conexion->prepare("INSERT INTO carrito (id_usuario, id_producto, cantidad) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        $stmt->execute();
        
        // Reducir stock disponible
        $stmt = $conexion->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $productId);
        $stmt->execute();
    }
    
    // Confirmar transacción
    $conexion->commit();
    
    echo json_encode(['success' => true, 'message' => 'Producto añadido al carrito']);
    
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