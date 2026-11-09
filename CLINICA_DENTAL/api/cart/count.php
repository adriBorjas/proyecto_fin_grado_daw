<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';

header('Content-Type: application/json');

// Inicializar count en 0
$count = 0;

try {
    // Verificar si el usuario está logueado
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $connection = connectDB();
        
        // Contar todos los productos en el carrito
        $stmt = $connection->prepare("
            SELECT SUM(cantidad) as total 
            FROM carrito 
            WHERE id_usuario = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $count = (int)$row['total'];
        }
        
        $stmt->close();
        closeDB($connection);
    }
    
    echo json_encode(['success' => true, 'count' => $count]);
} catch (Exception $e) {
    error_log("Error en count.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'count' => 0, 'error' => 'Error interno']);
}
?>