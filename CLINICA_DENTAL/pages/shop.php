<?php
include '../includes/header.php';
require_once '../includes/db.php';

// Añade la inclusión del CSS para alertas de stock
echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/stock-alerts.css">';

$connection = connectDB();
// Modificar esta consulta para solo mostrar productos con stock > 0
$stmt = $connection->prepare("SELECT * FROM productos WHERE stock > 0 ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="shop-section">
    <div class="container">
        <h2>Tienda</h2>
        
        <div class="product-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($product = $result->fetch_assoc()) {
                    ?>
                    <div class="product-card" aria-labelledby="product-title-<?php echo $product['id']; ?>">
                        <div class="product-image">
                            <img src="<?php echo SITE_URL . '/' . $product['imagen']; ?>" alt="<?php echo $product['nombre']; ?>" loading="lazy">
                        </div>
                        <div class="product-info">
                            <h3 id="product-title-<?php echo $product['id']; ?>"><?php echo $product['nombre']; ?></h3>
                            <p class="product-description"><?php echo $product['descripcion']; ?></p>
                            <p class="product-price"><?php echo number_format($product['precio'], 2, ',', '.'); ?> €</p>
                            
                            <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                            <div class="stock-alert">
                                <p>¡Solo quedan <?php echo $product['stock']; ?> unidades!</p>
                            </div>
                            <?php endif; ?>
                            
                            <button class="btn btn-carrito agregar-al-carrito" data-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-shopping-cart"></i> Añadir al carrito
                            </button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p class="no-products">No hay productos disponibles en este momento.</p>';
            }
            
            closeDB($connection);
            ?>
        </div>
    </div>
</section>

<script src="<?php echo SITE_URL; ?>/assets/js/shop.js?v=<?php echo time(); ?>"></script>

<?php include '../includes/footer.php'; ?>