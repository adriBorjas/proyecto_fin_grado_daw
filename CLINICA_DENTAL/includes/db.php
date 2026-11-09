<?php
require_once 'config.php';
require_once 'error_handler.php';

/**
 * Establece una conexión con la base de datos
 * 
 * @return mysqli Objeto de conexión a la base de datos
 */
function connectDB() {
    static $conexion = null;
    
    // Si ya existe una conexión, verificar que sigue activa
    if ($conexion !== null) {
        // Verificar si la conexión sigue activa
        if (!$conexion->ping()) {
            $conexion = null; // Resetear para crear nueva conexión
        } else {
            return $conexion;
        }
    }
    
    // Datos de conexión desde config.php
    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $db = DB_NAME;
    
    try {
        // Crear nueva conexión
        $conexion = new mysqli($host, $user, $pass, $db);
        
        // Verificar si hay errores de conexión
        if ($conexion->connect_errno) {
            throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
        }
        
        // Establecer el conjunto de caracteres
        $conexion->set_charset("utf8");
        
        return $conexion;
    } catch (Exception $e) {
        // Manejar el error
        ErrorHandler::handleDBError("No se pudo conectar a la base de datos", $e);
        exit;
    }
}

/**
 * Cierra la conexión a la base de datos
 * 
 * @param mysqli $conexion Conexión a cerrar
 */
function closeDB($conexion) {
    if ($conexion instanceof mysqli && !$conexion->connect_errno) {
        $conexion->close();
    }
}
?>