<?php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';
require_once '../../includes/upload.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/admin/dashboard.php');
    exit;
}

// Validar y recoger datos del formulario
$productId = (int)($_POST['product_id'] ?? 0);
$imagenActual = $_POST['imagen_actual'] ?? '';
$name = trim($_POST['nombre'] ?? '');
$description = trim($_POST['descripcion'] ?? '');
$price = floatval(str_replace(',', '.', $_POST['precio'] ?? 0));
$stock = floatval($_POST['stock'] ?? 0);

// Redondear el precio a 2 decimales para asegurarse
$price = round($price, 2);

// Validaciones básicas
$errors = [];

if ($productId <= 0) $errors[] = 'ID de producto no válido.';
if (empty($name)) $errors[] = 'El nombre del producto es obligatorio.';
if (empty($description)) $errors[] = 'La descripción es obligatoria.';
if ($price <= 0) $errors[] = 'El precio debe ser mayor que cero.';
if ($stock < 0) $errors[] = 'El stock no puede ser negativo.';

// Si hay errores, redirigir de vuelta al formulario
if (!empty($errors)) {
    $_SESSION['product_errors'] = $errors;
    header('Location: ' . SITE_URL . '/pages/admin/edit_product.php?id=' . $productId);
    exit;
}

$imageName = $imagenActual; // Por defecto, mantener la imagen actual

// Procesar imagen solo si se ha subido una nueva
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
    $image = $_FILES['imagen'];
    
    // Validar el contenido real y obtener una extensión segura
    $imageValidation = validateProductImage($image);
    if (isset($imageValidation['error'])) {
        $_SESSION['product_errors'] = [$imageValidation['error']];
        header('Location: ' . SITE_URL . '/pages/admin/edit_product.php?id=' . $productId);
        exit;
    }
    
    // Función para optimizar la imagen (igual que en add_product.php)
    function optimizarImagen($rutaOriginal, $rutaDestino, $calidad = 75) {
        // Comprobar si GD está disponible
        if (!function_exists('imagecreatetruecolor')) {
            // Si GD no está disponible, simplemente copiar el archivo
            return copy($rutaOriginal, $rutaDestino);
        }
        
        // Obtener información de la imagen
        $info = getimagesize($rutaOriginal);
        if ($info === false) {
            return false;
        }
        
        $mime = $info['mime'];
        
        // Crear imagen desde el archivo original
        switch ($mime) {
            case 'image/jpeg':
                $origen = imagecreatefromjpeg($rutaOriginal);
                break;
            case 'image/png':
                $origen = imagecreatefrompng($rutaOriginal);
                break;
            case 'image/gif':
                $origen = imagecreatefromgif($rutaOriginal);
                break;
            case 'image/webp':
                $origen = imagecreatefromwebp($rutaOriginal);
                break;
            case 'image/avif':
                if (function_exists('imagecreatefromavif')) {
                    $origen = imagecreatefromavif($rutaOriginal);
                } else {
                    return copy($rutaOriginal, $rutaDestino);
                }
                break;
            default:
                return false;
        }
        
        if (!$origen) {
            return false;
        }
        
        // Obtener dimensiones originales
        $ancho_original = imagesx($origen);
        $alto_original = imagesy($origen);
        
        // Definir dimensiones máximas
        $max_ancho = 800;
        $max_alto = 600;
        
        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($max_ancho / $ancho_original, $max_alto / $alto_original);
        
        if ($ratio < 1) {
            $nuevo_ancho = (int)($ancho_original * $ratio);
            $nuevo_alto = (int)($alto_original * $ratio);
        } else {
            $nuevo_ancho = $ancho_original;
            $nuevo_alto = $alto_original;
        }
        
        // Crear nueva imagen redimensionada
        $imagenTmp = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);
        
        // Preservar transparencia para PNG y GIF
        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($imagenTmp, false);
            imagesavealpha($imagenTmp, true);
            $transparent = imagecolorallocatealpha($imagenTmp, 255, 255, 255, 127);
            imagefill($imagenTmp, 0, 0, $transparent);
        }
        
        // Redimensionar imagen
        imagecopyresampled($imagenTmp, $origen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho_original, $alto_original);
        
        // Guardar imagen optimizada
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($imagenTmp, $rutaDestino, $calidad);
                break;
            case 'image/png':
                imagepng($imagenTmp, $rutaDestino, 6); // 0-9, menor = mejor calidad
                break;
            case 'image/gif':
                imagegif($imagenTmp, $rutaDestino);
                break;
            case 'image/webp':
                imagewebp($imagenTmp, $rutaDestino, $calidad);
                break;
            case 'image/avif':
                if (function_exists('imageavif')) {
                    imageavif($imagenTmp, $rutaDestino, $calidad);
                } else {
                    imagepng($imagenTmp, $rutaDestino, 6);
                }
                break;
            default:
                return copy($rutaOriginal, $rutaDestino);
        }
        
        // Liberar memoria
        imagedestroy($imagenTmp);
        imagedestroy($origen);
        
        return true;
    }
    
    // Generar nombre único usando la extensión del MIME validado
    $extension = $imageValidation['extension'];
    $nombreBase = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $name));
    $nuevoNombreImagen = $nombreBase . '_' . uniqid() . '.' . $extension;
    $rutaTemporal = '../../assets/img/temp/' . $nuevoNombreImagen;
    $rutaDestino = '../../assets/img/products/' . $nuevoNombreImagen;
    
    // Crear directorio temporal si no existe
    if (!file_exists('../../assets/img/temp/')) {
        mkdir('../../assets/img/temp/', 0755, true);
    }
    
    // Mover archivo subido a directorio temporal
    if (!move_uploaded_file($image['tmp_name'], $rutaTemporal)) {
        $_SESSION['product_errors'] = ['Error al subir la imagen.'];
        header('Location: ' . SITE_URL . '/pages/admin/edit_product.php?id=' . $productId);
        exit;
    }
    
    // Optimizar la imagen
    if (!optimizarImagen($rutaTemporal, $rutaDestino)) {
        $_SESSION['product_errors'] = ['Error al procesar la imagen.'];
        unlink($rutaTemporal);
        header('Location: ' . SITE_URL . '/pages/admin/edit_product.php?id=' . $productId);
        exit;
    }
    
    // Eliminar el archivo temporal
    unlink($rutaTemporal);
    
    $imageName = $nuevoNombreImagen;
}

try {
    // Conectar a la base de datos
    $conexion = connectDB();
    
    // Si se cambió la imagen, eliminar la anterior
    if ($imageName !== $imagenActual && !empty($imagenActual)) {
        $rutaImagenAnterior = '../../assets/img/products/' . $imagenActual;
        if (file_exists($rutaImagenAnterior)) {
            unlink($rutaImagenAnterior);
        }
    }
    
    // Actualizar producto - USAR RUTA RELATIVA como en add_product.php
    if ($imageName !== $imagenActual) {
        // Si se subió una nueva imagen, usar ruta relativa
        $rutaRelativa = 'assets/img/products/' . $imageName;
    } else {
        // Si no se cambió la imagen, mantener la ruta actual
        $rutaRelativa = $imagenActual;
    }
    
    $stmt = $conexion->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, stock = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("ssdisd", $name, $description, $price, $stock, $rutaRelativa, $productId);

    if ($stmt->execute()) {
        // Éxito: redirigir con mensaje de confirmación
        $_SESSION['mensaje'] = 'Producto actualizado correctamente.';
        header('Location: ' . SITE_URL . '/pages/admin/edit_product.php?id=' . $productId);
        exit;
    } else {
        // Error: redirigir con mensaje de error
        $_SESSION['product_errors'] = ['Error al actualizar el producto: ' . $conexion->error];
        header('Location: ' . SITE_URL . '/pages/admin/edit_product.php?id=' . $productId);
        exit;
    }
    
} catch (Exception $e) {
    // Capturar cualquier excepción
    $_SESSION['product_errors'] = ['Error inesperado: ' . $e->getMessage()];
    header('Location: ' . SITE_URL . '/pages/admin/edit_product.php?id=' . $productId);
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
