<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/error_handler.php';

header('Content-Type: application/json');

// Verificar si hay sesión activa
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['valid_session' => false]);
    exit;
}

// Verificar que el usuario existe en la base de datos
$userId = $_SESSION['user_id'];
$conexion = connectDB();
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userExists = $result->num_rows > 0;

$stmt->close();
closeDB($conexion);

echo json_encode(['valid_session' => $userExists]);
?>