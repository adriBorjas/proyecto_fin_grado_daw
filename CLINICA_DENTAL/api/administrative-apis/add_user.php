<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/admin/add_user.php');
    exit;
}

// Obtener y sanear los datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? ''; // Nuevo campo
$direccion = trim($_POST['direccion'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

// Validaciones básicas
$errores = [];

if (empty($nombre)) $errores[] = 'El nombre de usuario es obligatorio.';
if (empty($email)) $errores[] = 'El email es obligatorio.';
if (empty($password)) $errores[] = 'La contraseña es obligatoria.';
if (empty($password2)) $errores[] = 'La confirmación de contraseña es obligatoria.';
if ($password !== $password2) $errores[] = 'Las contraseñas no coinciden.';

// Validar formato del nombre
if (!preg_match('/^[a-zA-Z0-9_-]{1,16}$/', $nombre)) {
    $errores[] = "El nombre solo puede contener letras (sin acentos, diéresis o ñ), números, guiones y guiones bajos, y tener máximo 16 caracteres.";
}

// Validar formato del email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El email introducido no es válido.";
}

// Validar complejidad de la contraseña
if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $password)) {
    $errores[] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
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
    
    // Iniciar transacción explícitamente
    $conexion->begin_transaction();
    
    // CAMBIO: Verificar todos los posibles errores sin salir al primero
    
    // Verificar si el nombre de usuario ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $errores[] = "Este nombre de usuario ya está registrado.";
    }
    
    // Verificar si el email ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $errores[] = "Este correo electrónico ya está registrado.";
    }
    
    // Verificar si el teléfono ya existe (solo si se proporcionó un teléfono)
    if (!empty($telefono)) {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ? AND telefono != ''");
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Este número de teléfono ya está asociado a otra cuenta.";
        }
    }
    
    // Si hay errores, guardar los datos enviados y redirigir
    if (!empty($errores)) {
        $_SESSION['errores_admin'] = $errores;
        // Guardar los valores del formulario (excepto contraseñas)
        $_SESSION['old_input_admin'] = [
            'nombre' => $nombre,
            'email' => $email,
            'direccion' => $direccion,
            'telefono' => $telefono
        ];
        header('Location: ' . SITE_URL . '/pages/admin/add_user.php');
        exit;
    }
    
    // Hash de la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar el nuevo usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, direccion, telefono) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $email, $hashed_password, $direccion, $telefono);
    
    if ($stmt->execute()) {
        // AÑADIR ESTA LÍNEA: Confirmar la transacción
        $conexion->commit();
        
        // Registro exitoso
        $_SESSION['mensaje'] = 'Usuario añadido correctamente.';
        header('Location: ' . SITE_URL . '/pages/admin/add_user.php');
        exit;
    } else {
        // Rollback en caso de error
        $conexion->rollback();
        
        // Error al registrar
        $_SESSION['errores_admin'] = ["Error al crear el usuario: " . $conexion->error];
        header('Location: ' . SITE_URL . '/pages/admin/add_user.php');
        exit;
    }
    
} catch (Exception $e) {
    // Capturar cualquier excepción
    $_SESSION['errores_admin'] = ["Error inesperado: " . $e->getMessage()];
    header('Location: ' . SITE_URL . '/pages/admin/add_user.php');
    exit;
} finally {
    // Cerrar conexiones
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>