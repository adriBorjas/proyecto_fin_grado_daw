<?php
class ErrorHandler {
    /**
     * Maneja un error de base de datos
     * 
     * @param string $message Mensaje de error
     * @param Exception $exception Excepción capturada (opcional)
     * @param string $redirect URL a la que redirigir si no se muestra la página de error
     * @param bool $showErrorPage Si true, muestra la página de error; si false, redirige con mensaje en sesión
     */
    public static function handleDBError($message, $exception = null, $redirect = null, $showErrorPage = true) {
        // Registrar el error en el log
        $errorDetails = $exception ? ' - ' . $exception->getMessage() : '';
        error_log('Error de base de datos: ' . $message . $errorDetails);
        
        // Crear un mensaje de error más descriptivo
        $userMessage = 'Error de base de datos: ' . $message;
        
        // Determinar el tipo específico de error de base de datos
        if ($exception) {
            $errorMsg = $exception->getMessage();
            
            if (strpos($errorMsg, 'Connection refused') !== false || 
                strpos($errorMsg, 'Unable to connect') !== false) {
                $userMessage = 'No se puede conectar al servidor de base de datos. Verifique que MySQL esté en funcionamiento.';
            }
            else if (strpos($errorMsg, 'Unknown database') !== false) {
                $userMessage = 'La base de datos "clinica_dental" no existe o no está configurada correctamente.';
            }
            else if (strpos($errorMsg, 'Access denied') !== false) {
                $userMessage = 'Error de acceso a la base de datos. Verifique las credenciales de conexión.';
            }
            else if (strpos($errorMsg, 'Table') !== false && strpos($errorMsg, 'doesn\'t exist') !== false) {
                $userMessage = 'Error en la estructura de la base de datos. Tablas faltantes o corruptas.';
            }
        }
        
        // Guardar mensaje para mostrar
        $_SESSION['error_message'] = $userMessage;
        
        if ($showErrorPage) {
            // Redirigir a la página de error
            header('Location: ' . SITE_URL . '/pages/error.php');
            exit;
        } else if ($redirect) {
            // Si no se muestra página de error, redirigir a otra página con mensaje en sesión
            $_SESSION['error'] = $userMessage;
            header('Location: ' . $redirect);
            exit;
        }
    }
    
    /**
     * Maneja un error de aplicación genérico
     * 
     * @param string $message Mensaje de error
     * @param Exception $exception Excepción capturada (opcional)
     * @param string $redirect URL a la que redirigir si no se muestra la página de error
     * @param bool $showErrorPage Si true, muestra la página de error; si false, redirige con mensaje en sesión
     */
    public static function handleAppError($message, $exception = null, $redirect = null, $showErrorPage = true) {
        // Registrar el error en el log
        $errorDetails = $exception ? ' - ' . $exception->getMessage() : '';
        error_log('Error de aplicación: ' . $message . $errorDetails);
        
        // Guardar mensaje para mostrar
        $_SESSION['error_message'] = $message;
        
        if ($showErrorPage) {
            // Redirigir a la página de error
            header('Location: ' . SITE_URL . '/pages/error.php');
            exit;
        } else if ($redirect) {
            // Si no se muestra página de error, redirigir a otra página con mensaje en sesión
            $_SESSION['error'] = $message;
            header('Location: ' . $redirect);
            exit;
        }
    }
    
    /**
     * Maneja un error de autenticación
     * 
     * @param string $message Mensaje de error
     * @param string $redirect URL a la que redirigir
     */
    public static function handleAuthError($message, $redirect = null) {
        // Registrar el error en el log
        error_log('Error de autenticación: ' . $message);
        
        // Por defecto, redirigir a la página de login
        $redirectUrl = $redirect ? $redirect : SITE_URL . '/pages/auth.php?action=login';
        
        $_SESSION['error'] = $message;
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    /**
     * Maneja un error de validación de formulario
     * 
     * @param array $errors Lista de errores
     * @param string $redirect URL a la que redirigir
     * @param string $sessionKey Clave de sesión para guardar los errores
     * @param array $oldInput Datos enviados por el usuario para repoblar el formulario
     * @param string $oldInputKey Clave de sesión para guardar los datos enviados
     */
    public static function handleValidationError($errors, $redirect, $sessionKey = 'errores', $oldInput = null, $oldInputKey = 'old_input') {
        // Guardar errores en sesión
        $_SESSION[$sessionKey] = $errors;
        
        // Guardar datos antiguos para repoblar el formulario
        if ($oldInput) {
            $_SESSION[$oldInputKey] = $oldInput;
        }
        
        // Redirigir de vuelta al formulario
        header('Location: ' . $redirect);
        exit;
    }
}
?>