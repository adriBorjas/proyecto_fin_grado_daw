<?php
// filepath: c:\xampp\htdocs\CLINICA_DENTAL\api\admin\delete_product.php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

// Verificar que es una petición POST con JSON
$input = json_decode(file_get_contents('php://input'), true);
header('Content-Type: application/json');

if (!isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de producto no proporcionado']);
    exit;
}

$productId = (int)$input['id'];

try {
    $conexion = connectDB();
    
    // Obtener información de la imagen antes de eliminar
    $stmt = $conexion->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        exit;
    }
    
    $producto = $resultado->fetch_assoc();
    $imagenRuta = '../../assets/img/products/' . $producto['imagen'];
    $stmt->close();
    
    // Deshabilitar temporalmente las restricciones de clave foránea
    $conexion->query('SET FOREIGN_KEY_CHECKS=0');
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Devolver stock de productos en carritos antes de eliminar
    $stmt = $conexion->prepare("
        SELECT SUM(c.cantidad) as total_cantidad 
        FROM carrito c 
        WHERE c.id_producto = ?
    ");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stockData = $result->fetch_assoc();

    if ($stockData['total_cantidad'] > 0) {
        // Devolver stock al producto antes de eliminarlo
        $updateStock = $conexion->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
        $updateStock->bind_param("ii", $stockData['total_cantidad'], $productId);
        $updateStock->execute();
        $updateStock->close();
    }
    $stmt->close();
    
    // 1. Eliminar registros del carrito relacionados con este producto
    // (No devolvemos stock porque el producto se elimina completamente)
    $conexion->query("DELETE FROM carrito WHERE id_producto = $productId");
    
    // 2. Verificar y eliminar de otras tablas si existen
    $checkHistorial = $conexion->query("SHOW TABLES LIKE 'historial_compras'");
    if ($checkHistorial->num_rows > 0) {
        $conexion->query("DELETE FROM historial_compras WHERE id_producto = $productId");
    }
    
    $checkPedidosItems = $conexion->query("SHOW TABLES LIKE 'pedidos_items'");
    if ($checkPedidosItems->num_rows > 0) {
        $conexion->query("DELETE FROM pedidos_items WHERE id_producto = $productId");
    }
    
    $checkReviews = $conexion->query("SHOW TABLES LIKE 'reviews'");
    if ($checkReviews->num_rows > 0) {
        $conexion->query("DELETE FROM reviews WHERE id_producto = $productId");
    }
    
    $checkFavoritos = $conexion->query("SHOW TABLES LIKE 'favoritos'");
    if ($checkFavoritos->num_rows > 0) {
        $conexion->query("DELETE FROM favoritos WHERE id_producto = $productId");
    }
    
    // 3. Eliminar el producto 
    $conexion->query("DELETE FROM productos WHERE id = $productId");
    
    // Restaurar restricciones de clave foránea
    $conexion->query('SET FOREIGN_KEY_CHECKS=1');
    
    // Confirmar transacción
    $conexion->commit();
    
    // Eliminar la imagen si existe
    if (file_exists($imagenRuta)) {
        @unlink($imagenRuta);
    }
    
    echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
    
} catch (Exception $e) {
    // Revertir transacción y restaurar restricciones en caso de error
    if (isset($conexion)) {
        $conexion->query('SET FOREIGN_KEY_CHECKS=1');
        $conexion->rollback();
    }
    
    // Registrar el error para análisis
    error_log("Error al eliminar producto ID $productId: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error al eliminar el producto: ' . $e->getMessage()
    ]);
    
} finally {
    // Asegurar que las restricciones estén activadas antes de cerrar
    if (isset($conexion)) {
        $conexion->query('SET FOREIGN_KEY_CHECKS=1');
        closeDB($conexion);
    }
}
?>