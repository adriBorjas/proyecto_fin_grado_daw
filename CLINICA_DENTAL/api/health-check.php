<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/error_handler.php';

header('Content-Type: application/json');

try {
    // Verificar conexión a la base de datos
    $conexion = connectDB();
    
    // Realizar una consulta simple para verificar que funciona correctamente
    $result = $conexion->query("SELECT 1");
    
    if (!$result) {
        throw new Exception("La base de datos no responde correctamente");
    }
    
    // Verificar espacio en disco
    $freeSpace = disk_free_space('/');
    $totalSpace = disk_total_space('/');
    $freePercentage = ($freeSpace / $totalSpace) * 100;
    
    // Si hay menos del 10% de espacio libre, advertir
    $diskWarning = $freePercentage < 10;
    
    echo json_encode([
        'success' => true,
        'database' => 'ok',
        'disk_space' => [
            'free_percentage' => round($freePercentage, 2),
            'warning' => $diskWarning
        ],
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la verificación de estado: ' . $e->getMessage()
    ]);
    
    // Registrar el error
    error_log('Error en health-check.php: ' . $e->getMessage());
} finally {
    // Cerrar conexión
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>