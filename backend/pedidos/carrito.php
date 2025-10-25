<?php
// 1. Incluimos la configuración (inicia sesión, BASE_URL)
require_once '../PHP/config.php';
// 2. Incluimos los datos de productos (para saber nombres y precios)
require_once '../PHP/datos_productos.php';

// 3. Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// 4. Calcular el total del carrito
$total_carrito = 0;
$total_items = 0;

foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
    if (isset($todos_los_productos[$id_producto])) {
        $producto = $todos_los_productos[$id_producto];
        $total_carrito += $producto['precio'] * $cantidad;
        $total_items += $cantidad;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - THE PRINT</title>
    
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/nav.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/footer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        .cart-item {
            transition: all 0.3s ease;
        }
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        .cantidad-control {
            max-width: 120px;
        }
        .btn-cantidad {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>

    <?php require_once '../PHP/nav.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4">
            <i class="bi bi-cart3 me-2"></i>
            Mi Carrito de Compras
        </h1>

        <!-- Mensajes -->
        <?php if (isset($_SESSION['mensaje_carrito'])): ?>
            <div class="alert alert-<?php echo $_SESSION['mensaje_carrito']['tipo']; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $_SESSION['mensaje_carrito']['tipo'] == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
                <?php 
                echo $_SESSION['mensaje_carrito']['texto']; 
                unset($_SESSION['mensaje_carrito']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['carrito'])): ?>
            <!-- Carrito vacío -->
            <div class="text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                <h3 class="mt-3 text-muted">Tu carrito está vacío</h3>
                <p class="text-muted">Agrega productos para comenzar tu compra</p>
                <a href="<?php echo BASE_URL; ?>backend/productos/productos.php" class="btn btn-primary mt-3">
                    <i class="bi bi-shop me-2"></i>
                    Ir a la Tienda
                </a>
            </div>
        <?php else: ?>
            <!-- Carrito con productos -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-bag-check me-2"></i>
                                Productos en tu carrito (<?php echo $total_items; ?>)
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th class="text-center">Precio Unitario</th>
                                            <th class="text-center">Cantidad</th>
                                            <th class="text-end">Subtotal</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['carrito'] as $id_producto => $cantidad): ?>
                                            <?php if (isset($todos_los_productos[$id_producto])): ?>
                                                <?php 
                                                $producto = $todos_los_productos[$id_producto];
                                                $subtotal = $producto['precio'] * $cantidad;
                                                ?>
                                                <tr class="cart-item" id="item-<?php echo $id_producto; ?>">
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo $producto['img']; ?>" 
                                                                 alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                                                                 class="me-3" 
                                                                 style="width: 60px; height: 60px; object-fit: contain;">
                                                            <div>
                                                                <h6 class="mb-0"><?php echo htmlspecialchars($producto['nombre']); ?></h6>
                                                                <small class="text-muted"><?php echo ucfirst($producto['tipo']); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <strong class="precio-unitario">
                                                            $ <?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                                                        </strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="input-group cantidad-control mx-auto">
                                                            <button class="btn btn-outline-secondary btn-cantidad" 
                                                                    type="button" 
                                                                    onclick="cambiarCantidad('<?php echo $id_producto; ?>', -1, <?php echo $producto['stock']; ?>, <?php echo $producto['precio']; ?>)">
                                                                <i class="bi bi-dash"></i>
                                                            </button>
                                                            <input type="number" 
                                                                   class="form-control text-center cantidad-input" 
                                                                   id="cantidad-<?php echo $id_producto; ?>"
                                                                   value="<?php echo $cantidad; ?>" 
                                                                   min="1" 
                                                                   max="<?php echo $producto['stock']; ?>"
                                                                   data-precio="<?php echo $producto['precio']; ?>"
                                                                   onchange="actualizarCantidad('<?php echo $id_producto; ?>', this.value, <?php echo $producto['stock']; ?>, <?php echo $producto['precio']; ?>)"
                                                                   style="max-width: 60px;">
                                                            <button class="btn btn-outline-secondary btn-cantidad" 
                                                                    type="button" 
                                                                    onclick="cambiarCantidad('<?php echo $id_producto; ?>', 1, <?php echo $producto['stock']; ?>, <?php echo $producto['precio']; ?>)">
                                                                <i class="bi bi-plus"></i>
                                                            </button>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">Stock: <?php echo $producto['stock']; ?></small>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="subtotal" id="subtotal-<?php echo $id_producto; ?>">
                                                            $ <?php echo number_format($subtotal, 0, ',', '.'); ?>
                                                        </strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmarEliminar('<?php echo $id_producto; ?>', '<?php echo htmlspecialchars($producto['nombre']); ?>')"
                                                                title="Eliminar producto">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-flex justify-content-between mt-3">
                        <a href="<?php echo BASE_URL; ?>backend/productos/productos.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Seguir Comprando
                        </a>
                        <form action="<?php echo BASE_URL; ?>backend/PHP/carrito_acciones.php" method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="vaciar">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('¿Estás seguro de vaciar el carrito?')">
                                <i class="bi bi-trash me-2"></i>
                                Vaciar Carrito
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Resumen del pedido -->
                <div class="col-lg-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-receipt me-2"></i>
                                Resumen del Pedido
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Productos:</span>
                                <span id="total-items"><?php echo $total_items; ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal-carrito">$ <?php echo number_format($total_carrito, 0, ',', '.'); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-success fs-4" id="total-carrito">
                                    $ <?php echo number_format($total_carrito, 0, ',', '.'); ?>
                                </strong>
                            </div>
                            
                            <a href="<?php echo BASE_URL; ?>checkout.php" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-credit-card me-2"></i>
                                Proceder al Pago
                            </a>
                            
                            <div class="alert alert-info mt-3 mb-0">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    Envío gratuito en compras superiores a $100.000
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once '../PHP/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para cambiar cantidad con botones +/-
        function cambiarCantidad(idProducto, cambio, stockMax, precioUnitario) {
            const input = document.getElementById('cantidad-' + idProducto);
            let nuevaCantidad = parseInt(input.value) + cambio;
            
            // Validar límites
            if (nuevaCantidad < 1) nuevaCantidad = 1;
            if (nuevaCantidad > stockMax) {
                alert('Stock máximo disponible: ' + stockMax);
                nuevaCantidad = stockMax;
            }
            
            input.value = nuevaCantidad;
            actualizarCantidad(idProducto, nuevaCantidad, stockMax, precioUnitario);
        }

        // Función para actualizar cantidad y recalcular totales
        function actualizarCantidad(idProducto, cantidad, stockMax, precioUnitario) {
            cantidad = parseInt(cantidad);
            
            // Validar
            if (cantidad < 1 || isNaN(cantidad)) {
                cantidad = 1;
            }
            if (cantidad > stockMax) {
                alert('Stock máximo disponible: ' + stockMax);
                cantidad = stockMax;
            }
            
            // Actualizar input
            document.getElementById('cantidad-' + idProducto).value = cantidad;
            
            // Calcular nuevo subtotal
            const nuevoSubtotal = precioUnitario * cantidad;
            
            // Actualizar subtotal en la tabla
            document.getElementById('subtotal-' + idProducto).textContent = 
                '$ ' + nuevoSubtotal.toLocaleString('es-CO');
            
            // Recalcular total del carrito
            recalcularTotal();
            
            // Enviar actualización al servidor
            actualizarServidor(idProducto, cantidad);
        }

        // Recalcular total del carrito
        function recalcularTotal() {
            let totalItems = 0;
            let totalCarrito = 0;
            
            // Recorrer todos los inputs de cantidad
            document.querySelectorAll('.cantidad-input').forEach(input => {
                const cantidad = parseInt(input.value);
                const precio = parseFloat(input.dataset.precio);
                
                totalItems += cantidad;
                totalCarrito += precio * cantidad;
            });
            
            // Actualizar interfaz
            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('subtotal-carrito').textContent = '$ ' + totalCarrito.toLocaleString('es-CO');
            document.getElementById('total-carrito').textContent = '$ ' + totalCarrito.toLocaleString('es-CO');
        }

        // Actualizar en el servidor
        function actualizarServidor(idProducto, cantidad) {
            const formData = new FormData();
            formData.append('action', 'actualizar');
            formData.append('id_producto', idProducto);
            formData.append('cantidad', cantidad);
            
            fetch('<?php echo BASE_URL; ?>backend/PHP/carrito_acciones.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    console.error('Error al actualizar el carrito');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Confirmar eliminación
        function confirmarEliminar(idProducto, nombreProducto) {
            if (confirm(`¿Estás seguro de eliminar "${nombreProducto}" del carrito?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?php echo BASE_URL; ?>backend/PHP/carrito_acciones.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'eliminar';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_producto';
                idInput.value = idProducto;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Auto-cerrar alertas después de 3 segundos
        setTimeout(function() {
            const alertas = document.querySelectorAll('.alert');
            alertas.forEach(alerta => {
                if (alerta.querySelector('.btn-close')) {
                    const bsAlert = new bootstrap.Alert(alerta);
                    bsAlert.close();
                }
            });
        }, 3000);
    </script>
</body>
</html>
