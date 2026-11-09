<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

header('Content-Type: application/json');
$userId = $_SESSION['user_id'];

try {
    $conexion = connectDB();
    
    // Verificar si hay productos en el carrito
    $stmt = $conexion->prepare("SELECT COUNT(*) as total FROM carrito WHERE id_usuario = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $row = $resultado->fetch_assoc();
    
    if ($row['total'] == 0) {
        echo json_encode(['success' => false, 'message' => 'No hay productos en el carrito']);
        exit;
    }
    
    // Iniciar transacción para asegurar la integridad de los datos
    $conexion->begin_transaction();
    
    // Obtener los productos del carrito con sus cantidades
    $stmt = $conexion->prepare("SELECT c.id_producto, c.cantidad, p.stock 
                               FROM carrito c 
                               JOIN productos p ON c.id_producto = p.id 
                               WHERE c.id_usuario = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($item = $result->fetch_assoc()) {
        // Verificar que haya suficiente stock
        if ($item['stock'] < $item['cantidad']) {
            $conexion->rollback();
            echo json_encode(['success' => false, 'message' => 'No hay suficiente stock disponible para alguno de los productos']);
            exit;
        }
    }
    
    // Eliminar productos del carrito (el stock ya fue descontado previamente)
    $stmt = $conexion->prepare("DELETE FROM carrito WHERE id_usuario = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Confirmar la transacción
    $conexion->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => '¡Compra realizada con éxito! Gracias por tu compra.',
        'items_purchased' => $row['total']
    ]);
    
} catch (Exception $e) {
    // Revertir la transacción en caso de error
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