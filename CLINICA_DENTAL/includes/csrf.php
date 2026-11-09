<?php
/**
 * Protección CSRF basada en un token almacenado en la sesión.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' .
        htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function request_expects_json() {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    return strpos($accept, 'application/json') !== false ||
        strpos($contentType, 'application/json') !== false ||
        strpos($scriptName, '/api/') !== false;
}

function require_csrf_token() {
    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['GET', 'HEAD', 'OPTIONS'], true)) {
        return;
    }

    $submittedToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!$sessionToken || !$submittedToken || !hash_equals($sessionToken, $submittedToken)) {
        http_response_code(403);

        if (request_expects_json()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Solicitud no válida. Recarga la página e inténtalo de nuevo.'
            ]);
        } else {
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Solicitud no válida. Recarga la página e inténtalo de nuevo.';
        }

        exit;
    }
}
