<?php
// 1. Incluimos la configuración (BD, BASE_URL)
require_once '../PHP/config.php';
// 2. Incluimos los datos de productos
require_once '../PHP/datos_productos.php';

// 3. Obtenemos la categoría de la URL
$tipo_seleccionado = isset($_GET['tipo']) ? $_GET['tipo'] : 'todos';

// 4. Obtenemos los productos filtrados usando la función
$productos_filtrados = obtener_productos_por_tipo($tipo_seleccionado);

// 5. Definimos un título para la página
$titulo_pagina = "Todos los Productos";
switch ($tipo_seleccionado) {
    case 'impresoras': $titulo_pagina = "Impresoras"; break;
    case 'consumibles': $titulo_pagina = "Tintas y Consumibles"; break;
    case 'papeleria': $titulo_pagina = "Papelería"; break;
    case 'repuestos': $titulo_pagina = "Repuestos y Accesorios"; break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - THE PRINT</title>
    
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/nav.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/footer.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style-productos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .btn-agregar-carrito {
            position: relative;
            overflow: hidden;
        }
        
        .btn-agregar-carrito.agregado {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }
        
        .cantidad-input {
            border-left: 0;
            border-right: 0;
        }
        
        .cantidad-input:focus {
            box-shadow: none;
            border-color: #dee2e6;
        }
        
        .input-group .btn {
            border-color: #dee2e6;
        }
        
        .input-group .btn:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
    </style>
</head>
<body>

    <!-- ======== 1. NAVEGACIÓN ======== -->
    <?php require_once '../PHP/nav.php'; ?>

    <!-- ======== 2. CONTENIDO DE LA PÁGINA ======== -->
    <div class="container my-5">
        
        <!-- Mensaje de confirmación cuando se agrega un producto -->
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
        
        <div class="row">

            <!-- ======== BARRA LATERAL (FILTROS) ======== -->
            <div class="col-lg-3">
                <h3 class="mb-4">Categorías</h3>
                <div class="list-group product-sidebar">
                    <a href="<?php echo BASE_URL; ?>backend/productos/productos.php?tipo=todos" 
                       class="list-group-item list-group-item-action <?php if($tipo_seleccionado == 'todos') echo 'active'; ?>">
                       <i class="bi bi-grid-3x3-gap me-2"></i> Todos los Productos
                    </a>
                    <a href="<?php echo BASE_URL; ?>backend/productos/productos.php?tipo=impresoras" 
                       class="list-group-item list-group-item-action <?php if($tipo_seleccionado == 'impresoras') echo 'active'; ?>">
                       <i class="bi bi-printer me-2"></i> Impresoras
                    </a>
                    <a href="<?php echo BASE_URL; ?>backend/productos/productos.php?tipo=consumibles" 
                       class="list-group-item list-group-item-action <?php if($tipo_seleccionado == 'consumibles') echo 'active'; ?>">
                       <i class="bi bi-droplet me-2"></i> Tintas y Consumibles
                    </a>
                    <a href="<?php echo BASE_URL; ?>backend/productos/productos.php?tipo=papeleria" 
                       class="list-group-item list-group-item-action <?php if($tipo_seleccionado == 'papeleria') echo 'active'; ?>">
                       <i class="bi bi-file-earmark-text me-2"></i> Papelería
                    </a>
                    <a href="<?php echo BASE_URL; ?>backend/productos/productos.php?tipo=repuestos" 
                       class="list-group-item list-group-item-action <?php if($tipo_seleccionado == 'repuestos') echo 'active'; ?>">
                       <i class="bi bi-tools me-2"></i> Repuestos y Accesorios
                    </a>
                </div>
                
                <!-- Resumen del carrito en la barra lateral -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-cart3 fs-1 text-primary"></i>
                        <h5 class="mt-2">Mi Carrito</h5>
                        <p class="mb-2">
                            <strong><?php echo contar_items_carrito(); ?></strong> 
                            <?php echo (contar_items_carrito() == 1) ? 'producto' : 'productos'; ?>
                        </p>
                        <a href="<?php echo BASE_URL; ?>backend/pedidos/carrito.php" class="btn btn-primary w-100">
                            <i class="bi bi-cart-check me-1"></i> Ver Carrito
                        </a>
                    </div>
                </div>
            </div>

            <!-- ======== LISTA DE PRODUCTOS ======== -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><?php echo $titulo_pagina; ?></h2>
                    <span class="badge bg-secondary">
                        <?php echo count($productos_filtrados); ?> productos
                    </span>
                </div>
                
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">

                    <?php if (empty($productos_filtrados)): ?>
                        <div class="col-12">
                            <div class="alert alert-warning" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No hay productos disponibles en esta categoría.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($productos_filtrados as $producto): ?>
                        <div class="col">
                            <div class="card h-100 product-card">
                                <img src="<?php echo $producto['img']; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                     style="height: 200px; object-fit: contain; padding: 15px;">
                                
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?php echo ucfirst(htmlspecialchars($producto['tipo'])); ?>
                                        <?php if (!empty($producto['descripcion'])): ?>
                                            <br><small><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                        <?php endif; ?>
                                    </p>
                                    
                                    <div class="mt-auto">
                                        <h4 class="card-price text-success mb-3">
                                            $ <?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                                        </h4>
                                        
                                        <?php if ($producto['stock'] > 0): ?>
                                            <small class="text-muted d-block mb-2">
                                                <i class="bi bi-box-seam"></i> 
                                                Stock: <?php echo $producto['stock']; ?> disponibles
                                            </small>
                                        <?php else: ?>
                                            <small class="text-danger d-block mb-2">
                                                <i class="bi bi-x-circle"></i> Sin stock
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-transparent border-0 pb-3">
                                    <?php if ($producto['stock'] > 0): ?>
                                        <form action="<?php echo BASE_URL; ?>backend/PHP/carrito_acciones.php" method="POST" class="agregar-carrito-form">
                                            <input type="hidden" name="action" value="agregar">
                                            <input type="hidden" name="id_producto" value="<?php echo $producto['id']; ?>">
                                            <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                            
                                            <!-- Selector de cantidad -->
                                            <div class="input-group input-group-sm mb-2">
                                                <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidad(this, -1)">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" name="cantidad" class="form-control text-center cantidad-input" 
                                                       value="1" min="1" max="<?php echo $producto['stock']; ?>" 
                                                       style="max-width: 60px;">
                                                <button class="btn btn-outline-secondary" type="button" onclick="cambiarCantidad(this, 1)">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary w-100 btn-agregar-carrito">
                                                <i class="bi bi-cart-plus me-1"></i> Añadir al Carrito
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="bi bi-x-circle me-1"></i> No Disponible
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <!-- ======== 3. PIE DE PÁGINA ======== -->
    <?php require_once '../PHP/footer.php'; ?>

    <!-- JS de Bootstrap -->
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Función para cambiar cantidad con botones + y -
        function cambiarCantidad(btn, cambio) {
            const input = btn.parentElement.querySelector('.cantidad-input');
            let valor = parseInt(input.value) || 1;
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            valor += cambio;
            
            if (valor < min) valor = min;
            if (valor > max) valor = max;
            
            input.value = valor;
        }
        
        // Efecto visual al hacer clic en "Agregar al carrito"
        document.querySelectorAll('.agregar-carrito-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const btn = this.querySelector('.btn-agregar-carrito');
                btn.classList.add('agregado');
                btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> ¡Agregado!';
            });
        });
        
        // Auto-cerrar alertas después de 3 segundos
        setTimeout(function() {
            const alertas = document.querySelectorAll('.alert');
            alertas.forEach(alerta => {
                const bsAlert = new bootstrap.Alert(alerta);
                bsAlert.close();
            });
        }, 3000);
    </script>
    <?php require_once '../PHP/chatbot.php'; ?>
</body>
</html>
