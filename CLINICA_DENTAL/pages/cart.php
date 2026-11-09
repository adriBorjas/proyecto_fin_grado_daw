<?php
include '../includes/header.php';
require_once '../includes/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/pages/auth.php?action=login');
    exit;
}

// Obtener productos del carrito
$userId = $_SESSION['user_id'];
$connection = connectDB();
$sql = "SELECT c.id, c.cantidad, p.id as producto_id, p.nombre, p.precio, p.imagen 
        FROM carrito c 
        JOIN productos p ON c.id_producto = p.id 
        WHERE c.id_usuario = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<section class="cart-section">
    <div class="container">
        <h2>Mi Carrito</h2>
        
        <?php if(isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['cart_message']; ?>
                <?php unset($_SESSION['cart_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if($result->num_rows > 0): ?>
            <div class="cart-items">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total = 0;
                        while($item = $result->fetch_assoc()): 
                            $subtotal = $item['cantidad'] * $item['precio'];
                            $total += $subtotal;
                        ?>
                            <tr data-product-id="<?php echo $item['producto_id']; ?>">
                                <td class="cart-product" data-label="Producto">
                                    <img src="<?php echo SITE_URL . '/' . $item['imagen']; ?>" alt="<?php echo $item['nombre']; ?>">
                                    <span><?php echo $item['nombre']; ?></span>
                                </td>
                                <td class="cart-price" data-label="Precio Unitario"><?php echo number_format($item['precio'], 2, ',', '.'); ?> €</td>
                                <td class="cart-quantity" data-label="Cantidad">
                                    <span class="quantity-label-mobile">Cantidad</span>
                                    <div class="cart-quantity-controls">
                                        <button class="quantity-decrease" data-id="<?php echo $item['id']; ?>">-</button>
                                        <span class="quantity-value"><?php echo $item['cantidad']; ?></span>
                                        <button class="quantity-increase" data-id="<?php echo $item['id']; ?>">+</button>
                                    </div>
                                </td>
                                <td class="cart-subtotal" data-label="Total"><?php echo number_format($subtotal, 2, ',', '.'); ?> €</td>
                                <td class="cart-actions" data-label="Acciones">
                                    <button class="btn-remove" data-id="<?php echo $item['id']; ?>">Eliminar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="cart-summary">Total</td>
                            <td colspan="2" class="cart-grand-total"><?php echo number_format($total, 2, ',', '.'); ?> €</td>
                        </tr>
                    </tfoot>
                </table>
                
                <div class="cart-buttons">
                    <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-secondary">Seguir comprando</a>
                    <button id="checkout-btn" class="btn btn-primary">Finalizar compra</button>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart fa-3x"></i>
                <p>Tu carrito está vacío</p>
                <a href="<?php echo SITE_URL; ?>/pages/shop.php" class="btn btn-ver-productos">
                    <i class="fas fa-shopping-bag"></i>
                    <span>Ver catálogo de productos</span>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="<?php echo SITE_URL; ?>/assets/js/cart.js"></script>

<?php 
closeDB($connection);
include '../includes/footer.php'; 
?>