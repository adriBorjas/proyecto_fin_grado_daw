<?php
// filepath: c:\xampp\htdocs\CLINICA_DENTAL\api\admin\add_product.php
require_once '../../includes/config.php';
require_once '../../includes/db.php';
require_once '../../includes/check_admin.php';
require_once '../../includes/upload.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
    exit;
}

// Validar y recoger datos del formulario
$name = trim($_POST['nombre'] ?? '');
$description = trim($_POST['descripcion'] ?? '');
$price = floatval(str_replace(',', '.', $_POST['precio'] ?? 0));
$stock = floatval($_POST['stock'] ?? 0);

// Redondear el precio a 2 decimales para asegurarse
$price = round($price, 2);

// Validaciones básicas
$errors = [];

if (empty($name)) $errors[] = 'El nombre del producto es obligatorio.';
if (empty($description)) $errors[] = 'La descripción es obligatoria.';
if ($price <= 0) $errors[] = 'El precio debe ser mayor que cero.';
if ($stock < 0) $errors[] = 'El stock no puede ser negativo.';

// Verificar si se ha subido una imagen
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors[] = 'Debe seleccionar una imagen para el producto.';
}

// Si hay errores, redirigir de vuelta al formulario
if (!empty($errors)) {
    $_SESSION['product_errors'] = $errors;
    header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
    exit;
}

// Procesar imagen
$image = $_FILES['imagen'];
$imageName = '';

// Validar el contenido real y obtener una extensión segura
$imageValidation = validateProductImage($image);
if (isset($imageValidation['error'])) {
    $_SESSION['product_errors'] = [$imageValidation['error']];
    header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
    exit;
}

// Función para optimizar la imagen
function optimizarImagen($rutaOriginal, $rutaDestino, $calidad = 75) {
    // Comprobar si GD está disponible
    if (!function_exists('imagecreatetruecolor')) {
        // Si GD no está disponible, simplemente copiar el archivo
        return copy($rutaOriginal, $rutaDestino);
    }
    
    // Si GD está disponible, procesar la imagen
    list($ancho, $alto, $tipo) = getimagesize($rutaOriginal);
    
    // Redimensionar automáticamente si es muy grande
    $maxWidth = 1200; // Ancho máximo para imágenes
    
    // Calcular nuevas dimensiones
    if ($ancho > $maxWidth) {
        $nuevoAncho = $maxWidth;
        $nuevoAlto = round(($alto / $ancho) * $nuevoAncho);
    } else {
        $nuevoAncho = $ancho;
        $nuevoAlto = $alto;
    }
    
    // Si no necesitamos redimensionar, simplemente copiar
    if ($nuevoAncho === $ancho && $nuevoAlto === $alto && $tipo === IMAGETYPE_JPEG) {
        return copy($rutaOriginal, $rutaDestino);
    }
    
    $imagenTmp = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
    
    switch ($tipo) {
        case IMAGETYPE_JPEG:
            $origen = @imagecreatefromjpeg($rutaOriginal);
            if (!$origen) {
                return copy($rutaOriginal, $rutaDestino);
            }
            imagecopyresampled($imagenTmp, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
            imagejpeg($imagenTmp, $rutaDestino, $calidad);
            break;
            
        case IMAGETYPE_PNG:
            $origen = @imagecreatefrompng($rutaOriginal);
            if (!$origen) {
                return copy($rutaOriginal, $rutaDestino);
            }
            // Preservar transparencia
            imagealphablending($imagenTmp, false);
            imagesavealpha($imagenTmp, true);
            imagecopyresampled($imagenTmp, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
            imagepng($imagenTmp, $rutaDestino, 6); // 0-9, menor = mejor calidad
            break;
            
        default:
            // Para otros formatos, simplemente copiar
            return copy($rutaOriginal, $rutaDestino);
    }
    
    // Liberar memoria
    imagedestroy($imagenTmp);
    if (isset($origen)) {
        imagedestroy($origen);
    }
    
    return true;
}

// Generar nombre único usando la extensión del MIME validado
$extension = $imageValidation['extension'];
$nombreBase = strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $name));
$imageName = $nombreBase . '_' . uniqid() . '.' . $extension;
$rutaTemporal = '../../assets/img/temp/' . $imageName;
$rutaDestino = '../../assets/img/products/' . $imageName;

// Añade esta línea para crear la ruta relativa que se guardará en la BD
$rutaRelativa = 'assets/img/products/' . $imageName;

// Crear directorio temporal si no existe
if (!file_exists('../../assets/img/temp/')) {
    mkdir('../../assets/img/temp/', 0755, true);
}

// Mover archivo subido a directorio temporal
if (!move_uploaded_file($image['tmp_name'], $rutaTemporal)) {
    $_SESSION['product_errors'] = ['Error al subir la imagen.'];
    header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
    exit;
}

// Optimizar la imagen
if (!optimizarImagen($rutaTemporal, $rutaDestino)) {
    $_SESSION['product_errors'] = ['Error al procesar la imagen.'];
    unlink($rutaTemporal);
    header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
    exit;
}

// Eliminar el archivo temporal
unlink($rutaTemporal);

try {
    // Conectar a la base de datos
    $conexion = connectDB();
    
    // Insertar nuevo producto
    $stmt = $conexion->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, imagen) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $name, $description, $price, $stock, $rutaRelativa);

    if ($stmt->execute()) {
        // Éxito: redirigir con mensaje de confirmación
        $_SESSION['mensaje'] = 'Producto añadido correctamente.';
        header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
        exit;
    } else {
        // Error: redirigir con mensaje de error
        $_SESSION['errores_producto'] = ['Error al guardar el producto: ' . $conexion->error];
        
        // Eliminar la imagen si hubo error en la base de datos
        if (file_exists($rutaDestino)) {
            unlink($rutaDestino);
        }
        
        header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
        exit;
    }
    
} catch (Exception $e) {
    // Capturar cualquier excepción
    $_SESSION['product_errors'] = ['Error inesperado: ' . $e->getMessage()];
    
    // Eliminar la imagen si hubo error
    if (file_exists($rutaDestino)) {
        unlink($rutaDestino);
    }
    
    header('Location: ' . SITE_URL . '/pages/admin/add_product.php');
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
