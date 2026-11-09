<?php
/**
 * Valida una imagen subida y devuelve su MIME y extensión segura.
 */
function validateProductImage($file) {
    if (!isset($file) || !is_array($file)) {
        return ['error' => 'No se ha recibido ninguna imagen.'];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['error' => 'La imagen no se pudo subir correctamente.'];
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['error' => 'La imagen subida no es válida.'];
    }

    if (($file['size'] ?? 0) <= 0 || $file['size'] > 5 * 1024 * 1024) {
        return ['error' => 'La imagen es demasiado grande. El tamaño máximo permitido es 5MB.'];
    }

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/avif' => 'avif'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
    if ($finfo) {
        finfo_close($finfo);
    }

    if (!isset($allowedTypes[$mime])) {
        return ['error' => 'El archivo no es una imagen permitida. Solo se aceptan JPEG, PNG, GIF, WEBP o AVIF.'];
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false || ($imageInfo['mime'] ?? '') !== $mime) {
        return ['error' => 'El contenido del archivo no corresponde a una imagen válida.'];
    }

    return [
        'mime' => $mime,
        'extension' => $allowedTypes[$mime]
    ];
}
