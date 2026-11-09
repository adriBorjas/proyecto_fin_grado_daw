<?php
/**
 * Sistema de logs para operaciones críticas
 */

function logActivity($userId, $action, $description, $status = 'success', $extraData = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logDir = __DIR__ . '/../logs';
    
    // Crear directorio si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/activity_' . date('Y-m-d') . '.log';
    
    // Preparar mensaje de log
    $message = "[{$timestamp}] [User: {$userId}] [{$status}] {$action}: {$description}";
    
    // Añadir datos extra si existen
    if ($extraData) {
        $jsonData = json_encode($extraData, JSON_UNESCAPED_UNICODE);
        $message .= " Data: {$jsonData}";
    }
    
    // Añadir información del cliente
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $message .= " | IP: {$ip} | Agent: {$userAgent}";
    
    // Escribir en el archivo de log
    file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
}

function logError($message, $errorData = null) {
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not_logged_in';
    logActivity($userId, 'ERROR', $message, 'error', $errorData);
}

function logCheckout($userId, $items, $total, $status) {
    logActivity($userId, 'CHECKOUT', "Compra por {$total}€", $status, ['items' => $items]);
}

function logLogin($userId, $status, $message) {
    logActivity($userId, 'LOGIN', $message, $status);
}

function logRegistration($userId, $status, $message) {
    logActivity($userId, 'REGISTER', $message, $status);
}