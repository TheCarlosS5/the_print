<?php
require_once './backend/PHP/config.php';

if (!isset($_SESSION['pedido_exitoso']) || !isset($_GET['id'])) {
    header('Location: ' . BASE_URL);
    exit();
}

$pedido_id = $_GET['id'];
$ultimo_pedido = $_SESSION['ultimo_pedido'] ?? null;

// Número de WhatsApp de tu negocio (CAMBIAR POR EL TUYO)
$whatsapp_numero = '573203150231'; // Formato: código país + número (sin +, espacios ni guiones)

// Construir mensaje completo de WhatsApp con todos los datos del pedido
$whatsapp_mensaje = "🛒 *PEDIDO #" . str_pad($pedido_id, 5, '0', STR_PAD_LEFT) . "*\n\n";

if ($ultimo_pedido) {
    // Información del cliente
    $whatsapp_mensaje .= "👤 *DATOS DEL CLIENTE*\n";
    $whatsapp_mensaje .= "📝 Nombre: " . $ultimo_pedido['nombre'] . "\n";
    $whatsapp_mensaje .= "📧 Email: " . $ultimo_pedido['email'] . "\n";
    $whatsapp_mensaje .= "📱 Teléfono: " . $ultimo_pedido['telefono'] . "\n\n";
    
    // Lista de productos
    $whatsapp_mensaje .= "🛍️ *PRODUCTOS ORDENADOS*\n";
    $whatsapp_mensaje .= "━━━━━━━━━━━━━━━━━━━━\n";
    
    foreach ($ultimo_pedido['productos'] as $index => $item) {
        $whatsapp_mensaje .= ($index + 1) . ". " . $item['nombre'] . "\n";
        $whatsapp_mensaje .= "   Cantidad: " . $item['cantidad'] . " x $" . number_format($item['precio'], 0, ',', '.') . "\n";
        $whatsapp_mensaje .= "   Subtotal: $" . number_format($item['subtotal'], 0, ',', '.') . "\n\n";
    }
    
    // Total del pedido
    $whatsapp_mensaje .= "━━━━━━━━━━━━━━━━━━━━\n";
    $whatsapp_mensaje .= "💰 *TOTAL: $" . number_format($ultimo_pedido['total'], 0, ',', '.') . "*\n\n";
    
    $whatsapp_mensaje .= "¿Podrían confirmarme el estado de mi pedido?\n\n";
    $whatsapp_mensaje .= "Gracias 😊";
} else {
    // Mensaje simple si no hay datos del pedido
    $whatsapp_mensaje .= "Hola, tengo una consulta sobre mi pedido.\n";
    $whatsapp_mensaje .= "¿Podrían darme información al respecto?\n\n";
    $whatsapp_mensaje .= "Gracias 😊";
}

$whatsapp_url = "https://wa.me/" . $whatsapp_numero . "?text=" . urlencode($whatsapp_mensaje);

unset($_SESSION['pedido_exitoso']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado - THE PRINT</title>
    
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/nav.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/footer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        .success-animation {
            animation: scaleIn 0.5s ease-in-out;
        }
        
        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }
        
        .whatsapp-btn {
            background: #25D366;
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .whatsapp-btn:hover {
            background: #128C7E;
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(37, 211, 102, 0.4);
        }
        
        .whatsapp-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .whatsapp-btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .info-card {
            border-left: 4px solid #667eea;
        }
        
        .producto-item {
            transition: background 0.3s ease;
        }
        
        .producto-item:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>

    <?php require_once './backend/PHP/nav.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Tarjeta principal de confirmación -->
                <div class="card shadow-lg border-0">
                    <div class="card-body py-5 text-center">
                        <div class="mb-4 success-animation">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 6rem;"></i>
                        </div>
                        
                        <h1 class="card-title mb-3 text-success">¡Pedido Confirmado!</h1>
                        
                        <p class="card-text text-muted fs-5 mb-4">
                            Tu pedido ha sido registrado exitosamente.
                        </p>
                        
                        <div class="alert alert-info info-card" role="alert">
                            <h4 class="alert-heading">
                                <i class="bi bi-receipt me-2"></i>
                                Número de Pedido
                            </h4>
                            <h2 class="mb-0 text-primary">#<?php echo str_pad($pedido_id, 5, '0', STR_PAD_LEFT); ?></h2>
                        </div>
                        
                        <?php if ($ultimo_pedido): ?>
                        <!-- Detalles del pedido -->
                        <div class="card mt-4 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-start mb-3">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Detalles del Cliente
                                </h5>
                                <div class="text-start">
                                    <p class="mb-2"><strong>Nombre:</strong> <?php echo htmlspecialchars($ultimo_pedido['nombre']); ?></p>
                                    <p class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($ultimo_pedido['email']); ?></p>
                                    <p class="mb-2"><strong>Teléfono:</strong> <?php echo htmlspecialchars($ultimo_pedido['telefono']); ?></p>
                                    <p class="mb-0"><strong>Total:</strong> 
                                        <span class="text-success fs-5">$ <?php echo number_format($ultimo_pedido['total'], 0, ',', '.'); ?></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Productos del pedido -->
                        <div class="card mt-3 border-0 bg-light">
                            <div class="card-body">
                                <h5 class="card-title text-start mb-3">
                                    <i class="bi bi-bag-check me-2"></i>
                                    Productos Ordenados
                                </h5>
                                <div class="list-group">
                                    <?php foreach ($ultimo_pedido['productos'] as $item): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center producto-item">
                                        <div class="text-start">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['nombre']); ?></h6>
                                            <small class="text-muted">
                                                Cantidad: <?php echo $item['cantidad']; ?> x 
                                                $<?php echo number_format($item['precio'], 0, ',', '.'); ?>
                                            </small>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            $ <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Información adicional -->
                        <div class="alert alert-success mt-4" role="alert">
                            <i class="bi bi-envelope-check me-2"></i>
                            <strong>Confirmación enviada</strong><br>
                            Hemos enviado un correo electrónico con los detalles de tu pedido.
                        </div>
                        
                        <!-- Vista previa del mensaje de WhatsApp -->
                        <?php if ($ultimo_pedido): ?>
                        <div class="card mt-4 border-success">
                            <div class="card-header bg-success text-white">
                                <i class="bi bi-whatsapp me-2"></i>
                                Vista Previa del Mensaje
                            </div>
                            <div class="card-body text-start" style="background: #e8f5e9;">
                                <small style="white-space: pre-line; font-family: monospace; color: #1b5e20;">
<?php echo htmlspecialchars($whatsapp_mensaje); ?>
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Botones de acción -->
                        <div class="d-grid gap-3 mt-4">
                            <!-- Botón de WhatsApp mejorado -->
                            <a href="<?php echo $whatsapp_url; ?>" 
                               target="_blank" 
                               class="btn btn-lg whatsapp-btn text-white">
                                <i class="bi bi-whatsapp me-2"></i>
                                Enviar Detalles del Pedido por WhatsApp
                            </a>
                            
                            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-house-door me-2"></i>
                                Volver al Inicio
                            </a>
                            
                            <a href="<?php echo BASE_URL; ?>backend/productos/productos.php?tipo=todos" 
                               class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-shop me-2"></i>
                                Seguir Comprando
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Información de seguimiento -->
                <div class="card mt-4 border-0 bg-light">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <i class="bi bi-info-circle me-2"></i>
                            ¿Qué sigue?
                        </h5>
                        <div class="row mt-3">
                            <div class="col-md-4 mb-3">
                                <i class="bi bi-clock-history text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0"><strong>1. Procesamiento</strong></p>
                                <small class="text-muted">Verificamos tu pedido</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="bi bi-box-seam text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0"><strong>2. Preparación</strong></p>
                                <small class="text-muted">Empacamos tus productos</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="bi bi-truck text-primary" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0"><strong>3. Envío</strong></p>
                                <small class="text-muted">Entregamos a tu dirección</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de ayuda adicional -->
                <div class="card mt-4 border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <div class="card-body text-center py-4">
                        <h5 class="card-title">
                            <i class="bi bi-headset me-2"></i>
                            ¿Necesitas Ayuda?
                        </h5>
                        <p class="mb-3">Nuestro equipo está disponible para resolver tus dudas</p>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small>
                                    <i class="bi bi-telephone-fill me-1"></i>
                                    Llámanos al: +57 300 123 4567
                                </small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <small>
                                    <i class="bi bi-envelope-fill me-1"></i>
                                    Email: soporte@theprint.com
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once './backend/PHP/footer.php'; ?>

    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Animación adicional al cargar la página
        window.addEventListener('load', function() {
            const successIcon = document.querySelector('.success-animation');
            if (successIcon) {
                setTimeout(() => {
                    successIcon.style.transform = 'scale(1)';
                }, 100);
            }
        });
    </script>
</body>
</html>
