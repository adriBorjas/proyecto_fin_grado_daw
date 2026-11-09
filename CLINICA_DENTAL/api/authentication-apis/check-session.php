<?php
require_once '../../includes/config.php';
header('Content-Type: application/json');

// Verificar si el usuario está logueado
$response = [
    'logged_in' => isset($_SESSION['user_id']),
    'user_name' => $_SESSION['user_name'] ?? null
];

echo json_encode($response);
exit;
?>