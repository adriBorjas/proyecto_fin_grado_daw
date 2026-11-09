<?php
// Configuración de sesiones para mayor estabilidad
ini_set('session.cookie_lifetime', 86400); // 24 horas
ini_set('session.gc_maxlifetime', 86400);  // 24 horas
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la base de datos.
// En local se puede crear config.local.php con los valores propios.
// Ese archivo está excluido de Git; si no existe, se usan los valores de ejemplo.
$localConfig = __DIR__ . '/config.local.php';
$exampleConfig = __DIR__ . '/config.example.php';
require_once is_file($localConfig) ? $localConfig : $exampleConfig;
require_once __DIR__ . '/csrf.php';
require_csrf_token();

// URL base del sitio desde LOCALHOST
//define('SITE_URL', '/CLINICA_DENTAL');

// URL base del sitio - cambiado para trabajar con DocumentRoot apuntando a CLINICA_DENTAL
define('SITE_URL', '');

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Incluir manejador global de excepciones
require_once __DIR__ . '/global_exception_handler.php';
?>
