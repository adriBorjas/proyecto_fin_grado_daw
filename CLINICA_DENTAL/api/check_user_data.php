<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/error_handler.php';

// Verificar que es una petición AJAX
header('Content-Type: application/json');

// Obtener los datos enviados
$type = isset($_POST['type']) ? $_POST['type'] : '';
$value = isset($_POST['value']) ? $_POST['value'] : '';
$response = ['exists' => false];

if (!empty($type) && !empty($value)) {
    $conexion = connectDB();
    
    // Preparar la consulta según el tipo de verificación
    if ($type === 'nombre') {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    } elseif ($type === 'email') {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    } elseif ($type === 'telefono') {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ? AND telefono != ''");
    }
    
    if (isset($stmt)) {
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['exists'] = true;
            
            // Mensaje personalizado según el campo
            if ($type === 'nombre') {
                $response['message'] = 'Este nombre de usuario ya está en uso';
            } elseif ($type === 'email') {
                $response['message'] = 'Este correo electrónico ya está registrado';
            } elseif ($type === 'telefono') {
                $response['message'] = 'Este número de teléfono ya está asociado a otra cuenta';
            }
        }
        
        $stmt->close();
    }
    
    closeDB($conexion);
}

echo json_encode($response);
?>