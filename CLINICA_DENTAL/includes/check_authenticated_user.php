<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

// Verificar que el usuario realmente existe en la base de datos
$userId = $_SESSION['user_id'];
$conexion = connectDB();
$checkUserStmt = $conexion->prepare("SELECT id FROM usuarios WHERE id = ?");
$checkUserStmt->bind_param("i", $userId);
$checkUserStmt->execute();
$userExists = $checkUserStmt->get_result()->num_rows > 0;
$checkUserStmt->close();

if (!$userExists) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Error de sesión: usuario no válido. Por favor, vuelve a iniciar sesión.'
    ]);
    
    // Limpiar la sesión
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_rol']);
    
    // Eliminar la cookie de respaldo si existe
    if (isset($_COOKIE['user_backup'])) {
        setcookie('user_backup', '', time() - 3600, '/');
    }
    
    exit;
}

// Si llegamos aquí, el usuario está autenticado y existe en la base de datos
?>