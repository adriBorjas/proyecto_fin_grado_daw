<?php
include_once '../../includes/header.php';
require_once '../../includes/db.php';
include_once '../../includes/check_admin.php';
?>

<section class="admin-section">
    <div class="container">
        <h2>Gestión de Productos</h2>
        
        <!-- Botón de volver al panel de administración -->
        <div class="admin-back-link">
            <a href="<?php echo SITE_URL; ?>/pages/admin/dashboard.php" class="btn-back-admin">
                <i class="fas fa-arrow-left"></i> Volver al panel de administración
            </a>
        </div>
        
        <!-- Formulario para añadir un producto -->
        <div class="admin-form-container">
            <h3>Añadir nuevo producto</h3>
            <form id="product-form" action="<?php echo SITE_URL; ?>/api/administrative-apis/add_product.php" method="post" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="nombre">Nombre del producto:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" required></textarea>
                </div>
                
                <!-- Para el campo de precio -->
                <div class="form-group">
                    <label for="precio">Precio:</label>
                    <input type="text" id="precio" name="precio" required>
                    <!-- El mensaje de error se añadirá dinámicamente aquí -->
                </div>
                
                <!-- Para el campo de stock -->
                <div class="form-group">
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" max="999999" required>
                    <!-- El mensaje de error se añadirá dinámicamente aquí -->
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <input type="text" id="categoria" name="categoria" required>
                </div>
                
                <div class="form-group">
                    <label for="imagen">Imagen del producto:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*" required>
                    <small>Recomendado: formato JPG o PNG, tamaño máximo 2MB</small>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Añadir Producto</button>
                </div>
            </form>
        </div>
        
        <!-- Lista de productos -->
        <div class="admin-list-container">
            <h3>Productos existentes</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $conexion = connectDB();
                    $sql = "SELECT * FROM productos ORDER BY id DESC";
                    $resultado = $conexion->query($sql);
                    
                    if ($resultado->num_rows > 0) {
                        while ($producto = $resultado->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $producto['id'] . "</td>";
                            echo "<td><img src='" . SITE_URL . "/assets/img/products/" . $producto['imagen'] . "' alt='" . $producto['nombre'] . "' width='50'></td>";
                            echo "<td>" . $producto['nombre'] . "</td>";
                            echo "<td>" . number_format($producto['precio'], 2, ',', '.') . " €</td>";
                            echo "<td>" . $producto['stock'] . "</td>";
                            echo "<td>" . $producto['categoria'] . "</td>";
                            echo "<td>
                                    <a href='edit_product.php?id=" . $producto['id'] . "' class='btn-edit'>Editar</a>
                                    <a href='#' class='btn-delete' data-id='" . $producto['id'] . "'>Eliminar</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No hay productos registrados.</td></tr>";
                    }
                    
                    closeDB($conexion);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="../../assets/js/admin.js"></script>
<script src="../../assets/js/admin-products.js"></script>

<?php include_once '../../includes/footer.php'; ?>
