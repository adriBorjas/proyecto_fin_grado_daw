<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/error_handler.php';

// Si no es una solicitud POST, redirigir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/auth.php?action=register');
    exit;
}

// Obtener y sanear los datos del formulario
$nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$password = $_POST['password'] ?? '';
$password2 = $_POST['password2'] ?? '';
$direccion = trim(filter_input(INPUT_POST, 'direccion', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$telefono = trim(filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

// Importante: capturar el parámetro de redirección
$redirect = $_POST['redirect'] ?? '';

// Validaciones básicas
$errores = [];

if (empty($nombre)) $errores[] = "El nombre de usuario es obligatorio.";
if (empty($email)) $errores[] = "El email es obligatorio.";
if (empty($password)) $errores[] = "La contraseña es obligatoria.";
if (empty($password2)) $errores[] = "La confirmación de contraseña es obligatoria.";
if ($password !== $password2) $errores[] = "Las contraseñas no coinciden.";

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
if (!empty($telefono) && !preg_match('/^6[0-9]{8}$/', $telefono)) {
    $errores[] = "El teléfono debe tener 9 dígitos numéricos y comenzar por 6.";
}

// Si hay errores, redirigir de vuelta al formulario
if (!empty($errores)) {
    $_SESSION['errores_registro'] = $errores;
    $_SESSION['old_input'] = [
        'nombre' => $nombre,
        'email' => $email,
        'direccion' => $direccion,
        'telefono' => $telefono
    ];
    
    // MODIFICAR ESTA PARTE PARA MANTENER EL PARÁMETRO REDIRECT
    $redirectUrl = SITE_URL . '/pages/auth.php?action=register';
    if (!empty($redirect)) {
        $redirectUrl .= '&redirect=' . urlencode($redirect);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
}

try {
    // Conectar a la base de datos
    $conexion = connectDB();
    
    // Iniciar transacción explícitamente
    $conexion->begin_transaction();
    
    // Verificar si el nombre de usuario ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $errores[] = "Este nombre de usuario ya está registrado.";
    }
    $stmt->close();
    
    // Verificar si el email ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        $errores[] = "Este correo electrónico ya está registrado.";
    }
    $stmt->close();
    
    // Verificar si el teléfono ya existe (solo si se proporcionó un teléfono)
    if (!empty($telefono)) {
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ? AND telefono != ''");
        $stmt->bind_param("s", $telefono);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows > 0) {
            $errores[] = "Este número de teléfono ya está asociado a otra cuenta.";
        }
        $stmt->close();
    }
    
    // Si hay errores de duplicados, hacer rollback y redirigir
    if (!empty($errores)) {
        $conexion->rollback();
        $_SESSION['errores_registro'] = $errores;
        $_SESSION['old_input'] = [
            'nombre' => $nombre,
            'email' => $email,
            'direccion' => $direccion,
            'telefono' => $telefono
        ];
        
        $redirectUrl = SITE_URL . '/pages/auth.php?action=register';
        if (!empty($redirect)) {
            $redirectUrl .= '&redirect=' . urlencode($redirect);
        }
        
        header('Location: ' . $redirectUrl);
        exit;
    }

    // Si todo está bien, continuar con el registro
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Verificar si los campos opcionales están vacíos y tratarlos como NULL
    if (empty($direccion)) $direccion = NULL;
    if (empty($telefono)) $telefono = NULL;
    
    // Insertar el nuevo usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, direccion, telefono) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $email, $hashed_password, $direccion, $telefono);
    
    if ($stmt->execute()) {
        // Obtener el ID generado
        $newUserId = $conexion->insert_id;
        
        // Confirmar la transacción
        $conexion->commit();
        
        // Establecer variables de sesión
        session_regenerate_id(true);
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_name'] = $nombre;
        $_SESSION['user_rol'] = 1; // Usuario normal
        
        // Registrar éxito
        error_log("Usuario registrado correctamente. ID: $newUserId, Nombre: $nombre");
        
        // Redirigir según el parámetro recibido
        if (!empty($redirect)) {
            error_log("Redirigiendo a: " . $redirect);
            
            switch ($redirect) {
                case 'appointments':
                    header('Location: ' . SITE_URL . '/pages/appointments.php?register_success=true');
                    break;
                case 'shop':
                    header('Location: ' . SITE_URL . '/pages/shop.php?register_success=true');
                    break;
                default:
                    header('Location: ' . SITE_URL . '/index.php?login_success=true&notification=register');
            }
        } else {
            // Redirección por defecto
            header('Location: ' . SITE_URL . '/index.php?login_success=true&notification=register');
        }
        exit;
    } else {
        // Si hay un error, hacer rollback
        $conexion->rollback();
        throw new Exception("Error al registrar el usuario: " . $conexion->error);
    }
} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($conexion)) {
        $conexion->rollback();
    }
    
    error_log("Error en registro: " . $e->getMessage());
    
    // Redirigir con mensaje de error
    $_SESSION['errores_registro'] = ["Ha ocurrido un error: " . $e->getMessage()];
    
    // ASEGURAR QUE ESTA PARTE TAMBIÉN MANTENGA EL REDIRECT
    $redirectUrl = SITE_URL . '/pages/auth.php?action=register';
    if (!empty($redirect)) {
        $redirectUrl .= '&redirect=' . urlencode($redirect);
    }
    
    header('Location: ' . $redirectUrl);
    exit;
} finally {
    // Cerrar conexión
    if (isset($conexion)) {
        closeDB($conexion);
    }
}
?>
