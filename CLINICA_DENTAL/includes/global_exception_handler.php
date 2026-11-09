<?php
require_once 'config.php';
require_once 'error_handler.php';

/**
 * Manejador global de excepciones no capturadas
 */
function globalExceptionHandler($exception) {
    $message = "Error no capturado: " . $exception->getMessage() . 
               " en " . $exception->getFile() . 
               " línea " . $exception->getLine();
    
    // Registrar el error con más detalle
    error_log($message);
    
    // Determinar si es una solicitud AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    
    // Crear un mensaje de error más descriptivo
    $userMessage = 'Ha ocurrido un error en el servidor.';
    
    // Determinar el tipo de error para mensajes más específicos
    if (strpos($exception->getMessage(), 'Connection refused') !== false || 
        strpos($exception->getMessage(), 'Unable to connect') !== false) {
        $userMessage = 'No se puede conectar al servidor de base de datos. Verifique que MySQL esté en funcionamiento.';
    }
    else if (strpos($exception->getMessage(), 'Unknown database') !== false) {
        $userMessage = 'La base de datos "clinica_dental" no existe o no está configurada correctamente.';
    }
    else if (strpos($exception->getMessage(), 'Access denied') !== false) {
        $userMessage = 'Error de acceso a la base de datos. Credenciales incorrectas.';
    }
    else if (strpos($exception->getMessage(), 'Table') !== false && strpos($exception->getMessage(), 'doesn\'t exist') !== false) {
        $userMessage = 'Error en la estructura de la base de datos. Tablas faltantes o corruptas.';
    }
    
    // No exponer detalles internos al usuario final
    $isDevelopment = false;
    
    if ($isAjax) {
        // Para peticiones AJAX, devolver JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $userMessage,
            'details' => $isDevelopment ? $exception->getMessage() : ''
        ]);
    } else {
        // Guardar un mensaje más específico en sesión
        $_SESSION['error_message'] = $userMessage;
        
        if ($isDevelopment) {
            $_SESSION['error_details'] = $exception->getMessage();
            $_SESSION['error_trace'] = $exception->getTraceAsString();
        }
        
        if (!headers_sent()) {
            header('Location: ' . SITE_URL . '/pages/error.php');
        } else {
            echo '<script>window.location.href="' . SITE_URL . '/pages/error.php";</script>';
        }
    }
    
    exit;
}

// Establecer el manejador global de excepciones
set_exception_handler('globalExceptionHandler');

/**
 * Manejador de errores para convertir errores en excepciones
 */
function errorToExceptionHandler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // Este código de error no está incluido en error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

// Establecer el manejador de errores
set_error_handler('errorToExceptionHandler');
?>
