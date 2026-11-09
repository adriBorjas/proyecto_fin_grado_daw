<?php
// filepath: c:\xampp\htdocs\CLINICA_DENTAL\api\auth\logout.php
require_once '../../includes/config.php';

// Vaciar los datos de sesión y eliminar la cookie asociada
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

// Redirigir a la página principal
header('Location: ' . SITE_URL . '/index.php');
exit;
?>
