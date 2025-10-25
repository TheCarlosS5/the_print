<?php
require_once './backend/PHP/config.php';
require_once './backend/PHP/datos_productos.php';

// Verificar carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    header('Location: ' . BASE_URL . 'backend/pedidos/carrito.php');
    exit();
}

// Generar token CSRF
$csrf_token = generate_csrf_token();

// Calcular total
$total_pedido = 0;
foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
    if (isset($todos_los_productos[$id_producto])) {
        $producto = $todos_los_productos[$id_producto];
        $total_pedido += $producto['precio'] * $cantidad;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - THE PRINT</title>
    
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/nav.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/footer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <?php require_once './backend/PHP/nav.php'; ?>

    <div class="container my-5">
        <h1 class="mb-4">Finalizar Compra</h1>

        <?php if (isset($_SESSION['error_pedido'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo escape_output($_SESSION['error_pedido']); 
                unset($_SESSION['error_pedido']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Datos de Envío</h5>
                        
                        <form action="<?php echo BASE_URL; ?>backend/PHP/guardar_pedido.php" method="POST" id="formCheckout">
                            <!-- Token CSRF -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre Completo *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       required minlength="2" maxlength="100"
                                       pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                                       title="Solo letras y espacios">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required maxlength="100">
                            </div>
                            
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono (10 dígitos) *</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       required pattern="[0-9]{10}" 
                                       placeholder="3001234567"
                                       title="Número de 10 dígitos">
                            </div>
                            
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección de Envío *</label>
                                <textarea class="form-control" id="direccion" name="direccion" 
                                          rows="3" required minlength="10" maxlength="500"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notas" class="form-label">Notas del Pedido (opcional)</label>
                                <textarea class="form-control" id="notas" name="notas" 
                                          rows="2" maxlength="1000"></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" id="btnSubmit">
                                    <i class="bi bi-check-circle me-2"></i>Confirmar Pedido
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Resumen del Pedido</h5>
                        
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($_SESSION['carrito'] as $id_producto => $cantidad): 
                                if (isset($todos_los_productos[$id_producto])):
                                    $producto = $todos_los_productos[$id_producto];
                                    $subtotal = $producto['precio'] * $cantidad;
                            ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0"><?php echo escape_output($producto['nombre']); ?></h6>
                                    <small class="text-muted">Cantidad: <?php echo $cantidad; ?></small>
                                </div>
                                <span>$ <?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                            </li>
                            <?php endif; endforeach; ?>
                        </ul>
                        
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Subtotal
                                <span>$ <?php echo number_format($total_pedido, 0, ',', '.'); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center fs-5 fw-bold">
                                Total
                                <span>$ <?php echo number_format($total_pedido, 0, ',', '.'); ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once './backend/PHP/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prevenir doble envío
        document.getElementById('formCheckout').addEventListener('submit', function() {
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
        });
    </script>
</body>
</html>
