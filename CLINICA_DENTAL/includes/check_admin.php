<?php
// Las API deben recibir una respuesta JSON; las páginas pueden redirigir.
$isApiRequest = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false;

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    if ($isApiRequest) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Autenticación requerida'
        ]);
        exit;
    }

    header('Location: ' . SITE_URL . '/pages/auth.php?action=login');
    exit;
}

try {
    // Verificar si el usuario tiene rol de administrador (rol = 0)
    $userId = $_SESSION['user_id'];
    $conexion = connectDB();
    
    // Usar una consulta más robusta
    $stmt = $conexion->prepare("SELECT rol FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Usar intval para asegurar que es una comparación numérica
        if (intval($user['rol']) !== 0) {
            $message = 'No tienes permisos de administrador.';
            $stmt->close();
            closeDB($conexion);

            if ($isApiRequest) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                exit;
            }

            $_SESSION['error'] = $message;
            header('Location: ' . SITE_URL . '/index.php');
            exit;
        }
    } else {
        // Usuario no encontrado en la base de datos
        $message = 'Usuario no encontrado.';
        $stmt->close();
        closeDB($conexion);

        if ($isApiRequest) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        }

        $_SESSION['error'] = $message;
        header('Location: ' . SITE_URL . '/pages/auth.php?action=login');
        exit;
    }
    
    // Cerrar solo el statement, no la conexión
    $stmt->close();
    // ¡NO cerrar la conexión aquí! Eliminar la línea: closeDB($conexion);
    
} catch (Exception $e) {
    // Registrar el error pero evitar mostrar información sensible
    error_log('Error en check_admin.php: ' . $e->getMessage());

    if ($isApiRequest) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'No se pudieron verificar los permisos de administrador.'
        ]);
        exit;
    }

    $_SESSION['error'] = 'Error al verificar permisos de administrador. Por favor, inténtalo de nuevo.';
    
    // No cerramos la conexión aquí
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}
?>
