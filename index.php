<?php
// 1. Incluimos la configuración PRIMERO
// Esta ruta apunta a tu nueva ubicación de config.php
require_once './backend/PHP/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE PRINT - Tu Tienda de Impresoras y Suministros</title>
    
    <!-- 2. Usamos BASE_URL para todas las rutas de CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/nav.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/footer.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style-index.css" rel="stylesheet"> 

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <!-- ======== 1. NAVEGACIÓN ======== -->
    <?php
        // Esta ruta sigue siendo correcta
        require './backend/PHP/nav.php';
    ?>

    <!-- ======== 2. BANNER ======== -->
    <header class="py-5 hero-banner">
        <div class="container px-5">
            <div class="row gx-5 justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center my-5">
                        <h1 class="display-4 fw-bolder text-white mb-2">Tu Tienda de Impresión</h1>
                        <p class="lead text-white-50 mb-4">Encuentra las mejores impresoras, tintas, toners y accesorios en un solo lugar.</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ======== 3. SECCIÓN DE CATEGORÍAS ======== -->
    <main class="container my-5" id="categorias">
        <h2 class="text-center mb-4 display-6">Nuestras Categorías</h2>
        
        <div class="row">

            <!-- Categoría 1: Impresoras -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 category-card">
                    <img src="assets/img/imprhp.jpg" class="card-img-top" alt="Categoría Impresoras">
                    <div class="card-body text-center">
                        <h5 class="card-title fs-4">Impresoras</h5>
                        <p class="card-text">Hogar, oficina, inyección, láser y más.</p>
                        <a href="<?php echo BASE_URL; ?>./backend/productos/productos.php?tipo=impresoras" class="btn btn-outline-primary">Ver Productos</a>
                    </div>
                </div>
            </div>
            
            <!-- Categoría 2: Tintas y Toners -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 category-card">
                    <img src="assets/img/tintasepson.jpg" class="card-img-top" alt="Categoría Tintas y Toners">
                    <div class="card-body text-center">
                        <h5 class="card-title fs-4">Tintas y Toners</h5>
                        <p class="card-text">Cartuchos originales y compatibles.</p>
                        <a href="<?php echo BASE_URL; ?>/backend/productos/productos.php?tipo=consumibles" class="btn btn-outline-primary">Ver Productos</a>
                    </div>
                </div>
            </div>

            <!-- Categoría 3: Papelería -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 category-card">
                    <img src="assets/img/cajahojas.jpg" class="card-img-top" alt="Categoría Papelería">
                    <div class="card-body text-center">
                        <h5 class="card-title fs-4">Papelería</h5>
                        <p class="card-text">Resmas de papel, fotográfico y especial.</p>
                        <a href="<?php echo BASE_URL; ?>/backend/productos/productos.php?tipo=papeleria" class="btn btn-outline-primary">Ver Productos</a>
                    </div>
                </div>
            </div>

            <!-- Categoría 4: Accesorios y Repuestos -->
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 category-card">
                    <img src="assets/img/repuestos.jpg" class="card-img-top" alt="Categoría Repuestos">
                    <div class="card-body text-center">
                        <h5 class="card-title fs-4">Repuestos</h5>
                        <p class="card-text">Cables, cabezales y kits de mantenimiento.</p>
                        <a href="<?php echo BASE_URL; ?>/backend/productos/productos.php?tipo=repuestos" class="btn btn-outline-primary">Ver Productos</a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- ======== 4. PIE DE PÁGINA ======== -->
    <?php
        include './backend/PHP/footer.php';
    ?>

    <!-- 4. Usamos BASE_URL para las rutas de JS -->
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    <?php require_once 'backend/PHP/chatbot.php'; ?>
</body>
</html>

