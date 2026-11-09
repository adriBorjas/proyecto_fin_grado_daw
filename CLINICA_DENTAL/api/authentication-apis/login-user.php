<?php
// filepath: c:\xampp\htdocs\CLINICA_DENTAL\api\auth\login.php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/error_handler.php';

// Si no es una solicitud POST, redirigir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/auth.php');
    exit;
}

// Obtener y sanear los datos del formulario
$nombre = trim(filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
$password = $_POST['password'];
$redirect = $_POST['redirect'] ?? '';

// Validaciones básicas
if (empty($nombre) || empty($password)) {
    $oldInput = ['nombre' => $nombre];
    $redirectUrl = SITE_URL . '/pages/auth.php?action=login';
    
    if (!empty($redirect)) {
        $redirectUrl .= '&redirect=' . urlencode($redirect);
    }
    
    ErrorHandler::handleValidationError(
        ["Por favor, completa todos los campos."], 
        $redirectUrl, 
        'errores_login', 
        $oldInput, 
        'old_input_login'
    );
    exit;
}

try {
    // Conectar a la base de datos
    $conexion = connectDB();
    
    // Buscar usuario por nombre
    $stmt = $conexion->prepare("SELECT id, nombre, email, password, rol FROM usuarios WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("El nombre de usuario o la contraseña son incorrectos");
    }
    
    $usuario = $result->fetch_assoc();
    
    // Verificar contraseña
    if (!password_verify($password, $usuario['password'])) {
        throw new Exception("El nombre de usuario o la contraseña son incorrectos");
    }
    
    // Iniciar sesión
    session_regenerate_id(true);
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_name'] = $usuario['nombre'];
    $_SESSION['user_rol'] = $usuario['rol'];
    
    // Verificar si hay citas canceladas
    require_once __DIR__ . '/../check_canceled_appointments.php';
    $userId = $_SESSION['user_id'];
    $canceledAppointments = checkCanceledAppointments($userId);
    
    if (!empty($canceledAppointments)) {
        // Si hay citas canceladas, mostrar notificación
        error_log("login-user.php - Usuario $userId tiene " . count($canceledAppointments) . " citas canceladas");
        $_SESSION['canceled_appointments'] = $canceledAppointments;
        header('Location: ' . SITE_URL . '/pages/profile.php?show_canceled=1');
        exit;
    }
    
    // Redirección según parámetro
    if (!empty($redirect)) {
        switch ($redirect) {
            case 'appointments':
                header('Location: ' . SITE_URL . '/pages/appointments.php?login_success=true');
                break;
            case 'shop':
                header('Location: ' . SITE_URL . '/pages/shop.php?login_success=true');
                break;
            default:
                header('Location: ' . SITE_URL . '/pages/profile.php');
                break;
        }
        exit;
    } else {
        header('Location: ' . SITE_URL . '/index.php?notification=login');
    }
    exit;
    
} catch (Exception $e) {
    error_log("Error en login: " . $e->getMessage());
    
    // Guardar datos del formulario
    $_SESSION['old_input_login'] = ['nombre' => $nombre];
    
    // Guardar mensaje de error
    $_SESSION['errores_login'] = [$e->getMessage()];
    
    // Redirigir
    $redirectUrl = SITE_URL . '/pages/auth.php?action=login';
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
