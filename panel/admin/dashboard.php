<?php
// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación PRIMERO
if(!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    header('Location: login.php');
    exit;
}

// Incluir archivos de configuración
require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar timeout de sesión
if(isset($_SESSION['admin_login_time'])) {
    if(time() - $_SESSION['admin_login_time'] > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
}

// Obtener estadísticas
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
    $total_pedidos = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'pendiente'");
    $pedidos_pendientes = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT SUM(total) as suma FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()");
    $ventas_hoy = $stmt->fetch()['suma'] ?? 0;
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()");
    $pedidos_hoy = $stmt->fetch()['total'];
    
    // Últimos 5 pedidos
    $stmt = $pdo->query("SELECT * FROM pedidos ORDER BY fecha_pedido DESC LIMIT 5");
    $ultimos_pedidos = $stmt->fetchAll();
    
    // Total de productos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $total_productos = $stmt->fetch()['total'];
    
    // Productos con bajo stock (menos de 5)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE stock < 5");
    $productos_bajo_stock = $stmt->fetch()['total'];
    
} catch(PDOException $e) {
    $error = "Error al cargar estadísticas: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - THE PRINT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 3rem;
            opacity: 0.8;
        }
        
        .clock-widget {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .clock-time {
            font-size: 3rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        .clock-date {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .recent-orders-widget {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 100%;
        }
        
        .order-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            transition: background 0.2s;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item:hover {
            background: #f8f9fa;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

    <!-- Header Admin -->
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </h2>
                    <small>
                        <i class="bi bi-person-circle me-1"></i>
                        <?php echo $_SESSION['admin_email'] ?? 'Administrador'; ?>
                    </small>
                </div>
                <div>
                    <a href="../../index.php" class="btn btn-light me-2">
                        <i class="bi bi-house-door me-1"></i>Sitio Web
                    </a>
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación por pestañas -->
    <div class="container mb-4">
        <ul class="nav nav-pills nav-fill shadow-sm" style="background: white; border-radius: 10px; padding: 0.5rem;">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="pedidos.php">
                    <i class="bi bi-receipt me-2"></i>Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="productos.php">
                    <i class="bi bi-box-seam me-2"></i>Productos
                </a>
            </li>
        </ul>
    </div>

    <div class="container">
        
        <?php if(isset($_GET['timeout'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="bi bi-clock-history me-2"></i>
            Tu sesión expiró por inactividad. Por favor inicia sesión nuevamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
        
        <!-- Banner de Bienvenida -->
        <div class="welcome-banner">
            <h3 class="mb-2">
                <i class="bi bi-emoji-smile me-2"></i>
                ¡Bienvenido de vuelta!
            </h3>
            <p class="mb-0">Panel de Administración - THE PRINT</p>
        </div>
        
        <!-- Estadísticas Generales -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="bi bi-receipt stat-icon text-primary"></i>
                        <h3 class="mt-3 mb-0"><?php echo $total_pedidos ?? 0; ?></h3>
                        <p class="text-muted mb-0">Total Pedidos</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="bi bi-clock-history stat-icon text-warning"></i>
                        <h3 class="mt-3 mb-0"><?php echo $pedidos_pendientes ?? 0; ?></h3>
                        <p class="text-muted mb-0">Pendientes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="bi bi-box-seam stat-icon text-info"></i>
                        <h3 class="mt-3 mb-0"><?php echo $total_productos ?? 0; ?></h3>
                        <p class="text-muted mb-0">Productos</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card stat-card text-center">
                    <div class="card-body">
                        <i class="bi bi-currency-dollar stat-icon text-success"></i>
                        <h3 class="mt-3 mb-0">$<?php echo number_format($ventas_hoy ?? 0, 0, ',', '.'); ?></h3>
                        <p class="text-muted mb-0">Ventas Hoy</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Widgets: Reloj y Pedidos Recientes -->
        <div class="row g-4">
            <!-- Reloj en Tiempo Real -->
            <div class="col-lg-4">
                <div class="clock-widget">
                    <i class="bi bi-clock fs-1 mb-3"></i>
                    <div class="clock-time" id="reloj">00:00:00</div>
                    <div class="clock-date" id="fecha">Cargando...</div>
                </div>
                
                <!-- Alertas -->
                <?php if ($productos_bajo_stock > 0): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong><?php echo $productos_bajo_stock; ?></strong> productos con stock bajo
                </div>
                <?php endif; ?>
                
                <?php if ($pedidos_pendientes > 0): ?>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong><?php echo $pedidos_pendientes; ?></strong> pedidos pendientes de procesar
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Pedidos Recientes -->
            <div class="col-lg-8">
                <div class="recent-orders-widget">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            Pedidos Recientes
                        </h5>
                        <a href="pedidos.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-arrow-right me-1"></i>
                            Ver Todos
                        </a>
                    </div>
                    
                    <?php if (empty($ultimos_pedidos)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1"></i>
                            <p class="mt-3">No hay pedidos recientes</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($ultimos_pedidos as $pedido): ?>
                        <div class="order-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>#<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($pedido['nombre_cliente']); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?php 
                                        echo $pedido['estado'] == 'pendiente' ? 'warning' : 
                                             ($pedido['estado'] == 'completado' ? 'success' : 
                                             ($pedido['estado'] == 'procesando' ? 'info' : 'danger')); 
                                    ?>">
                                        <?php echo ucfirst($pedido['estado']); ?>
                                    </span>
                                    <br>
                                    <strong class="text-success">
                                        $<?php echo number_format($pedido['total'], 0, ',', '.'); ?>
                                    </strong>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i>
                                <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Accesos Rápidos -->
        <div class="row g-4 mt-2">
            <div class="col-md-4">
                <a href="pedidos.php" class="card stat-card text-decoration-none">
                    <div class="card-body text-center">
                        <i class="bi bi-receipt-cutoff fs-1 text-primary mb-3"></i>
                        <h5>Gestionar Pedidos</h5>
                        <p class="text-muted mb-0">Ver y administrar todos los pedidos</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="productos.php" class="card stat-card text-decoration-none">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam fs-1 text-success mb-3"></i>
                        <h5>Gestionar Productos</h5>
                        <p class="text-muted mb-0">Agregar, editar o eliminar productos</p>
                    </div>
                </a>
            </div>
            
            <div class="col-md-4">
                <a href="../../index.php" class="card stat-card text-decoration-none" target="_blank">
                    <div class="card-body text-center">
                        <i class="bi bi-shop fs-1 text-info mb-3"></i>
                        <h5>Ver Tienda</h5>
                        <p class="text-muted mb-0">Visitar el sitio web público</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Reloj en tiempo real
        function actualizarReloj() {
            const ahora = new Date();
            
            // Formato de hora: HH:MM:SS
            const horas = String(ahora.getHours()).padStart(2, '0');
            const minutos = String(ahora.getMinutes()).padStart(2, '0');
            const segundos = String(ahora.getSeconds()).padStart(2, '0');
            
            document.getElementById('reloj').textContent = `${horas}:${minutos}:${segundos}`;
            
            // Formato de fecha: Día, DD de Mes de YYYY
            const opciones = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const fechaTexto = ahora.toLocaleDateString('es-CO', opciones);
            document.getElementById('fecha').textContent = fechaTexto.charAt(0).toUpperCase() + fechaTexto.slice(1);
        }
        
        // Actualizar cada segundo
        actualizarReloj();
        setInterval(actualizarReloj, 1000);
    </script>
</body>
</html>
