<?php
include_once '../../includes/header.php';
require_once '../../includes/db.php';
include_once '../../includes/check_admin.php';

// Verificar que se ha pasado un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . SITE_URL . '/pages/admin/dashboard.php');
    exit;
}

$productId = (int)$_GET['id'];

// Obtener datos del producto
$conexion = connectDB();
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $_SESSION['admin_error'] = 'Producto no encontrado.';
    header('Location: ' . SITE_URL . '/pages/admin/dashboard.php');
    exit;
}

$producto = $resultado->fetch_assoc();
$stmt->close();
closeDB($conexion);
?>

<!-- Asegurar que se cargan todos los estilos necesarios -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-dashboard.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin-custom.css?v=<?php echo time(); ?>">

<section class="admin-section">
    <div class="container">
        <div class="auth-forms">
            <h2 class="form-title">Editar Producto: <?php echo htmlspecialchars($producto['nombre']); ?></h2>
            
            <?php if(isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['mensaje']; ?>
                    <?php unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['product_errors']) && !empty($_SESSION['product_errors'])): ?>
                <div class="error-container">
                    <?php foreach($_SESSION['product_errors'] as $error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['product_errors']); ?>
                </div>
            <?php endif; ?>
            
            <form class="auth-form" action="../../api/administrative-apis/update_product.php" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="product_id" value="<?php echo $producto['id']; ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo $producto['imagen']; ?>">
                
                <div class="form-group">
                    <input type="text" id="nombre" name="nombre" placeholder="Nombre del producto" 
                           value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>
                <div class="form-group">
                    <textarea id="descripcion" name="descripcion" placeholder="Descripción del producto" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>
                <div class="form-group">
                    <input type="text" id="precio" name="precio" placeholder="Precio (€)" 
                           pattern="^\d+([,\.]\d{0,2})?$" 
                           title="Ingrese un número con máximo 2 decimales"
                           value="<?php echo number_format($producto['precio'], 2, ',', ''); ?>"
                           required onkeypress="return isNumberKeyWithDecimal(event, this)">
                    <small class="input-help">Formato: 10 o 10,50 (máximo 2 decimales)</small>
                </div>
                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" max="999999" 
                           value="<?php echo $producto['stock']; ?>" required>
                    <!-- El mensaje de error se añadirá dinámicamente aquí -->
                </div>
                <div class="form-group">
                    <label for="imagen" class="file-label">Imagen del producto:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                    <small class="input-help">Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP, AVIF. Tamaño máximo: 5MB. Dejar vacío para mantener la imagen actual.</small>
                    <div class="image-preview-container"></div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Actualizar Producto</button>
                </div>
            </form>
            
            <!-- Botón de volver al panel de administración -->
            <div class="admin-back-link">
                <a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php" class="btn-back-admin">
                    <i class="fas fa-arrow-left"></i> Volver al panel de administración
                </a>
            </div>
        </div>
    </div>
</section>

<script src="<?php echo SITE_URL; ?>/assets/js/admin.js?v=<?php echo time(); ?>"></script>
<?php include_once '../../includes/footer.php'; ?>
