<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

// Verificar que es una petición POST con JSON
$input = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$userId = (int)$input['id'];

// Proteger contra eliminación del administrador
$conexion = connectDB();
$stmt = $conexion->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$usuario = $resultado->fetch_assoc();
$stmt->close();

// Verificar que no se está eliminando al administrador
if ($usuario['nombre'] === 'dentista') {
    echo json_encode(['success' => false, 'message' => 'No se puede eliminar al usuario administrador']);
    exit;
}

try {
    // Deshabilitar temporalmente las restricciones de clave foránea
    $conexion->query('SET FOREIGN_KEY_CHECKS=0');
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // 1. Devolver stock de productos en carrito antes de eliminar
    $stmt = $conexion->prepare("
        SELECT c.id_producto, c.cantidad 
        FROM carrito c 
        WHERE c.id_usuario = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $cartResult = $stmt->get_result();
    
    while ($cartItem = $cartResult->fetch_assoc()) {
        // Devolver stock al producto
        $updateStock = $conexion->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $updateStock->bind_param("ii", $cartItem['cantidad'], $cartItem['id_producto']);
        $updateStock->execute();
        $updateStock->close();
    }
    $stmt->close();
    
    // 2. Eliminar las notificaciones de cancelaciones
    $conexion->query("DELETE FROM notificaciones_cancelaciones WHERE id_usuario = $userId");
    
    // 3. Eliminar registros del carrito (después de devolver stock)
    $conexion->query("DELETE FROM carrito WHERE id_usuario = $userId");
    
    // 4. Eliminar citas
    $conexion->query("DELETE FROM citas WHERE id_usuario = $userId");
    
    // 5. Verificar la tabla historial_compras y eliminar registros si existe
    $checkHistorial = $conexion->query("SHOW TABLES LIKE 'historial_compras'");
    if ($checkHistorial->num_rows > 0) {
        $conexion->query("DELETE FROM historial_compras WHERE id_usuario = $userId");
    }
    
    // 6. Verificar si hay otras tablas relacionadas y eliminar
    $checkPedidos = $conexion->query("SHOW TABLES LIKE 'pedidos'");
    if ($checkPedidos->num_rows > 0) {
        $conexion->query("DELETE FROM pedidos WHERE id_usuario = $userId");
    }
    
    $checkReviews = $conexion->query("SHOW TABLES LIKE 'reviews'");
    if ($checkReviews->num_rows > 0) {
        $conexion->query("DELETE FROM reviews WHERE id_usuario = $userId");
    }
    
    $checkFavoritos = $conexion->query("SHOW TABLES LIKE 'favoritos'");
    if ($checkFavoritos->num_rows > 0) {
        $conexion->query("DELETE FROM favoritos WHERE id_usuario = $userId");
    }
    
    // 7. Finalmente, eliminar el usuario
    $conexion->query("DELETE FROM usuarios WHERE id = $userId");
    
    // Restaurar restricciones de clave foránea
    $conexion->query('SET FOREIGN_KEY_CHECKS=1');
    
    // Confirmar transacción
    $conexion->commit();
    
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
    
} catch (Exception $e) {
    // Revertir transacción y restaurar restricciones en caso de error
    $conexion->query('SET FOREIGN_KEY_CHECKS=1');
    $conexion->rollback();
    
    // Registrar el error para análisis
    error_log("Error al eliminar usuario ID $userId: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error al eliminar el usuario: ' . $e->getMessage()
    ]);
    
} finally {
    // Asegurar que las restricciones estén activadas antes de cerrar
    $conexion->query('SET FOREIGN_KEY_CHECKS=1');
    closeDB($conexion);
}
?>