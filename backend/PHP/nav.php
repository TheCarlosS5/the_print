<?php
// Incluimos la configuración (BASE_URL)
require_once __DIR__ . '/config.php';

// Verificar si hay sesión de administrador activa
$es_admin = isset($_SESSION['admin_sesion']) && $_SESSION['admin_sesion'] === true;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top main-nav">
  <div class="container-fluid">
    
    <a class="navbar-brand fs-4" href="<?php echo BASE_URL; ?>index.php">
      <i class="bi bi-printer-fill me-2"></i>THE PRINT
    </a>

    <!-- Botón Hamburguesa para Móvil -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Enlaces de Navegación -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="<?php echo BASE_URL; ?>index.php">
            <i class="bi bi-house-door me-1"></i>Inicio
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>backend/productos/productos.php">
            <i class="bi bi-box-seam me-1"></i>Productos
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>backend/contacto/contacto.php">
            <i class="bi bi-envelope me-1"></i>Contacto
          </a>
        </li>
        
        <!-- Carrito de Compras -->
        <li class="nav-item ms-lg-3 mt-2 mt-lg-0"> 
          <a class="btn btn-success btn-cart position-relative" href="<?php echo BASE_URL; ?>backend/pedidos/carrito.php">
            <i class="bi bi-cart-fill me-1"></i>
            Carrito
            <?php 
            // Mostrar cantidad de productos en el carrito
            if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])): 
                $total_items = array_sum($_SESSION['carrito']);
                if ($total_items > 0):
            ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?php echo $total_items; ?>
              <span class="visually-hidden">productos en el carrito</span>
            </span>
            <?php endif; endif; ?>
          </a>
        </li>
        
        <!-- Botón Admin / Logout -->
        <?php if ($es_admin): ?>
          <!-- Si está logueado como admin, mostrar Dashboard y Cerrar Sesión -->
          <li class="nav-item dropdown ms-lg-2 mt-2 mt-lg-0">
            <a class="btn btn-outline-warning dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-shield-lock-fill me-1"></i>
              Administrador
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>panel/admin/dashboard.php">
                  <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>panel/admin/logout.php">
                  <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                </a>
              </li>
            </ul>
          </li>
        <?php else: ?>
          <!-- Si NO está logueado, mostrar botón de Iniciar Sesión -->
          <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
            <a class="btn btn-outline-light" href="<?php echo BASE_URL; ?>panel/admin/login.php">
              <i class="bi bi-shield-lock me-1"></i>
              Admin
            </a>
          </li>
        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>

<style>
  .btn-cart {
    transition: all 0.3s ease;
  }
  
  .btn-cart:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(25, 135, 84, 0.4);
  }
  
  .navbar-nav .btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
  }
  
  .dropdown-menu {
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }
  
  .dropdown-item {
    transition: all 0.2s ease;
  }
  
  .dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
  }
</style>
