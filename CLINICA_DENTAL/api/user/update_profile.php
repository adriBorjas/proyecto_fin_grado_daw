<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/error_handler.php';
require_once __DIR__ . '/../../includes/check_authenticated_user.php';

// Verificar que el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/pages/auth.php?action=login');
    exit;
}

// Si no es una solicitud POST, redirigir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/edit_profile.php');
    exit;
}

// Obtener y sanear los datos del formulario
$userId = $_SESSION['user_id'];
$nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';
$direccion = trim(filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$telefono = trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

// Validaciones básicas
$errores = [];

if (empty($nombre)) $errores[] = "El nombre de usuario es obligatorio.";
if (empty($email)) $errores[] = "El email es obligatorio.";

// Validar contraseñas solo si se proporciona una nueva
$cambiarPassword = false;
if (!empty($password) || !empty($password2)) {
    if ($password !== $password2) {
        $errores[] = "Las contraseñas no coinciden.";
    }
    
    // Validar complejidad de la contraseña
    if (!empty($password) && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $password)) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
    }
    
    $cambiarPassword = true;
}

// Validar formato del nombre
if (!preg_match('/^[a-zA-Z0-9_-]{1,16}$/', $nombre)) {
    $errores[] = "El nombre solo puede contener letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos, y tener máximo 16 caracteres.";
}

// Validar formato del email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El email introducido no es válido.";
}

// Validar teléfono si está presente
if (!empty($telefono)) {
    if (!preg_match('/^6[0-9]{8}$/', $telefono)) {
        $errores[] = "El teléfono debe tener 9 dígitos numéricos y comenzar por 6.";
    }
}

try {
    // Conectar a la base de datos
    $conexion = connectDB();
    
    // Obtener datos actuales del usuario
    $stmt = $conexion->prepare("SELECT nombre, email, telefono FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$usuario) {
        throw new Exception("No se pudo encontrar la información del usuario.");
    }
    
    // Verificar si el nombre de usuario ya existe (si es diferente al actual)
    if ($nombre !== $usuario['nombre']) {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ? AND id != ?");
        $stmt->bind_param("si", $nombre, $userId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Este nombre de usuario ya está registrado.";
        }
        $stmt->close();
    }
    
    // Verificar si el email ya existe (si es diferente al actual)
    if ($email !== $usuario['email']) {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Este correo electrónico ya está registrado.";
        }
        $stmt->close();
    }
    
    // Verificar si el teléfono ya existe (si es diferente al actual y no está vacío)
    if (!empty($telefono) && $telefono !== $usuario['telefono']) {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ? AND telefono != '' AND id != ?");
        $stmt->bind_param("si", $telefono, $userId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Este número de teléfono ya está asociado a otra cuenta.";
        }
        $stmt->close();
    }
    
    // Si hay errores, redirigir con mensajes
    if (!empty($errores)) {
        $_SESSION['error'] = implode("<br>", $errores);
        header('Location: ' . SITE_URL . '/pages/edit_profile.php');
        exit;
    }
    
    // Actualizar datos del usuario
    if ($cambiarPassword) {
        // Si se cambia la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, password = ?, direccion = ?, telefono = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nombre, $email, $hashed_password, $direccion, $telefono, $userId);
    } else {
        // Si no se cambia la contraseña
        $stmt = $conexion->prepare("UPDATE usuarios SET nombre = ?, email = ?, direccion = ?, telefono = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $nombre, $email, $direccion, $telefono, $userId);
    }
    
    if ($stmt->execute()) {
        // Actualizar nombre en la sesión
        $_SESSION['user_name'] = $nombre;
        
        $_SESSION['mensaje'] = "Datos actualizados correctamente.";
        header('Location: ' . SITE_URL . '/pages/profile.php');
        exit;
    } else {
        throw new Exception("Error al actualizar los datos: " . $conexion->error);
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header('Location: ' . SITE_URL . '/pages/edit_profile.php');
    exit;
} finally {
    // Cerrar conexión
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>