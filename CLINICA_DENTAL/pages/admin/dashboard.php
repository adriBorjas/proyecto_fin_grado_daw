<?php
// Iniciar captura de salida para evitar problemas con headers
ob_start();

try {
    include_once '../../includes/header.php';
    require_once '../../includes/db.php';
    include_once '../../includes/check_admin.php';
    
    // Agregar los estilos específicos del admin dashboard
    echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin-dashboard.css?v=' . time() . '">';
    echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin-custom.css?v=' . time() . '">';
    
    // Obtener información del usuario
    $userId = $_SESSION['user_id'];
    $connection = connectDB();
    $stmt = $connection->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        throw new Exception("No se pudo obtener información del usuario.");
    }
    
    $user = $result->fetch_assoc();
    $currentUser = $user; // Guardar referencia del usuario actual
    ?>

    <section class="admin-section">
        <div class="container">
            <h2>Panel de Administración</h2>

            <?php if(isset($_SESSION['mensaje']) && !empty($_SESSION['mensaje'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['mensaje']; ?>
                    <?php unset($_SESSION['mensaje']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error']) && !empty($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="admin-panel">
                <div class="admin-cards">
                    <!-- Gestión de Usuarios -->
                    <div class="admin-card usuarios-card">
                        <h4>Gestión de Usuarios</h4>
                        <div class="admin-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/admin/add_user.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Añadir Usuario
                            </a>
                            <a href="#" class="btn btn-danger" id="btn-delete-user">
                                <i class="fas fa-user-minus"></i> Eliminar Usuario
                            </a>
                        </div>
                        
                        <!-- Lista para eliminar usuario (oculta inicialmente) -->
                        <div class="admin-list" id="delete-user-list" style="display: none;">
                            <h5>Selecciona un Usuario para Eliminar</h5>
                            <div class="user-list">
                                <?php
                                $stmt = $connection->prepare("SELECT id, nombre, email FROM usuarios WHERE id != ? ORDER BY nombre");
                                $stmt->bind_param("i", $userId); // No mostrar el usuario actual
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    echo '<ul>';
                                    while ($user = $result->fetch_assoc()) {
                                        echo '<li>' . 
                                             htmlspecialchars($user['nombre']) . ' (' . htmlspecialchars($user['email']) . ')' . 
                                             '<button class="btn-delete-confirm" data-id="' . $user['id'] . '">Eliminar</button>' . 
                                             '</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<p>No hay usuarios para mostrar.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gestión de Productos -->
                    <div class="admin-card productos-card">
                        <h4>Gestión de Productos</h4>
                        <div class="admin-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/admin/add_product.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Añadir Producto
                            </a>
                            <a href="#" class="btn btn-danger" id="btn-delete-product">
                                <i class="fas fa-trash-alt"></i> Eliminar Producto
                            </a>
                            <a href="#" class="btn btn-warning btn-edit-full-width" id="btn-edit-product">
                                <i class="fas fa-edit"></i> Editar Producto
                            </a>
                        </div>
                        
                        <!-- Lista para editar producto (oculta inicialmente) -->
                        <div class="admin-list" id="edit-product-list" style="display: none;">
                            <h5>Selecciona un Producto para Editar</h5>
                            <div class="product-list">
                                <?php
                                $stmt = $connection->prepare("SELECT id, nombre, precio FROM productos ORDER BY nombre");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    echo '<ul>';
                                    while ($product = $result->fetch_assoc()) {
                                        echo '<li data-id="' . $product['id'] . '">' . 
                                             $product['nombre'] . ' (' . number_format($product['precio'], 2, ',', '.') . '€)' . 
                                             '<a href="' . SITE_URL . '/pages/admin/edit_product.php?id=' . $product['id'] . '" class="btn-edit-product-confirm" data-id="' . $product['id'] . '">Editar</a>' . 
                                             '</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<p>No hay productos para mostrar.</p>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Lista para eliminar producto (oculta inicialmente) -->
                        <div class="admin-list" id="delete-product-list" style="display: none;">
                            <h5>Selecciona un Producto para Eliminar</h5>
                            <div class="product-list">
                                <?php
                                $stmt = $connection->prepare("SELECT id, nombre, precio FROM productos ORDER BY nombre");
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if ($result->num_rows > 0) {
                                    echo '<ul>';
                                    while ($product = $result->fetch_assoc()) {
                                        echo '<li data-id="' . $product['id'] . '">' . 
                                             $product['nombre'] . ' (' . number_format($product['precio'], 2, ',', '.') . '€)' . 
                                             '<button class="btn-delete-product-confirm" data-id="' . $product['id'] . '">Eliminar</button>' . 
                                             '</li>';
                                    }
                                    echo '</ul>';
                                } else {
                                    echo '<p>No hay productos para mostrar.</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Gestión de disponibilidad - AHORA DEBAJO DE CITAS -->
                    <div class="admin-card disponibilidad-card">
                        <h4>Gestión de Disponibilidad</h4>
                        <p class="card-description">Configura los horarios de atención y bloquea días completos para citas.</p>
                        <div class="admin-actions">
                            <a href="<?php echo SITE_URL; ?>/pages/admin/manage_availability.php" class="btn btn-primary">
                                <i class="fas fa-calendar-alt"></i> Administrar Calendario
                            </a>
                        </div>
                        
                        <div class="availability-summary">
                            <h5>Próximos días bloqueados</h5>
                            <?php
                            // Obtener días bloqueados
                            $stmt = $connection->prepare("
                                SELECT fecha, motivo FROM dias_no_disponibles
                                WHERE fecha >= CURDATE()
                                ORDER BY fecha ASC
                                LIMIT 5
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result && $result->num_rows > 0) {
                                echo '<ul class="blocked-days-list">';
                                while ($row = $result->fetch_assoc()) {
                                    $fechaFormateada = date('d/m/Y', strtotime($row['fecha']));
                                    $motivo = htmlspecialchars($row['motivo']);
                                    echo "<li><strong>$fechaFormateada</strong>: $motivo</li>";
                                }
                                echo '</ul>';
                            } else {
                                echo '<p>No hay días bloqueados próximamente.</p>';
                            }
                            ?>
                            
                            <!-- Nueva sección para horarios bloqueados -->
                            <h5 class="mt-4">Días con horarios específicos bloqueados</h5>
                            <?php
                            // Obtener días con horarios específicos bloqueados
                            $stmt = $connection->prepare("
                                SELECT DISTINCT fecha FROM horarios_especificos
                                WHERE fecha >= CURDATE() AND disponible = 0
                                ORDER BY fecha ASC
                                LIMIT 5
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result && $result->num_rows > 0) {
                                echo '<ul class="blocked-hours-list">';
                                while ($row = $result->fetch_assoc()) {
                                    $fechaFormateada = date('d/m/Y', strtotime($row['fecha']));
                                    // Contar cuántos horarios están bloqueados ese día
                                    $countStmt = $connection->prepare("
                                        SELECT COUNT(*) as total FROM horarios_especificos
                                        WHERE fecha = ? AND disponible = 0
                                    ");
                                    $countStmt->bind_param('s', $row['fecha']);
                                    $countStmt->execute();
                                    $countResult = $countStmt->get_result();
                                    $countRow = $countResult->fetch_assoc();
                                    $totalBloqueados = $countRow['total'];
                                    
                                    echo "<li><strong>$fechaFormateada</strong>: $totalBloqueados horario(s) bloqueado(s)</li>";
                                    $countStmt->close();
                                }
                                echo '</ul>';
                            } else {
                                echo '<p>No hay días con horarios específicos bloqueados próximamente.</p>';
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Sección para administradores - Ver citas de otros usuarios -->
                    <div class="admin-card citas-card">
                        <h4>Gestión de Citas de Usuarios</h4>
                        <div class="admin-appointments-list">
                            <?php
                            // Consulta para obtener todas las citas con información de usuario
                            $stmt = $connection->prepare("
                                SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, u.nombre, u.email, u.telefono
                                FROM citas c 
                                JOIN usuarios u ON c.id_usuario = u.id
                                WHERE c.estado = 'activa' OR c.estado IS NULL
                                ORDER BY c.fecha DESC, c.hora_inicio ASC
                            ");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                echo '<table class="appointments-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Hora</th>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Teléfono</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                                    
                                while ($appointment = $result->fetch_assoc()) {
                                    $formattedDate = date('d/m/Y', strtotime($appointment['fecha']));
                                    $startTime = substr($appointment['hora_inicio'], 0, 5);
                                    $endTime = substr($appointment['hora_fin'], 0, 5);
                                    
                                    echo '<tr>';
                                    echo '<td>' . $formattedDate . '</td>';
                                    echo '<td>' . $startTime . ' - ' . $endTime . '</td>';
                                    echo '<td>' . htmlspecialchars($appointment['nombre']) . '</td>';
                                    echo '<td>' . htmlspecialchars($appointment['email']) . '</td>';
                                    echo '<td>' . (empty($appointment['telefono']) ? 'No disponible' : htmlspecialchars($appointment['telefono'])) . '</td>';
                                    echo '<td>
                                            <button class="btn-cancel-admin-appointment" data-id="' . $appointment['id'] . '">
                                                <i class="fas fa-times-circle"></i> Cancelar
                                            </button>
                                          </td>';
                                    echo '</tr>';
                                }
                                
                                echo '</tbody></table>';
                            } else {
                                echo '<p>No hay citas programadas por ningún usuario.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    // Variable global para uso en los scripts
    const SITE_URL = '<?php echo SITE_URL; ?>';
    </script>
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js?v=<?php echo time(); ?>"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/admin-appointments.js?v=<?php echo time(); ?>"></script>
    <?php 
    include_once '../../includes/footer.php';
    
} catch (Exception $e) {
    // Limpiar el buffer de salida
    ob_clean();
    
    // Registrar el error
    error_log('Error en dashboard.php: ' . $e->getMessage());
    
    // Guardar el mensaje de error para mostrar en la página de error
    $_SESSION['error_message'] = 'Error al cargar el panel de administración: ' . $e->getMessage();
    
    // Redirigir a la página de error
    header('Location: ' . SITE_URL . '/pages/error.php');
    exit;
} finally {
    // Cerrar la conexión si existe
    if (isset($connection)) {
        closeDB($connection);
    }
    
    // Enviar el output buffer
    ob_end_flush();
}
?>