<?php
// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if(!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar timeout
if(isset($_SESSION['admin_login_time'])) {
    if(time() - $_SESSION['admin_login_time'] > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: login.php?timeout=1');
        exit;
    }
}

// Obtener pedidos con filtros
try {
    $filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $por_pagina = 15;
    $offset = ($pagina - 1) * $por_pagina;
    
    $sql = "SELECT * FROM pedidos";
    if($filtro_estado) {
        $sql .= " WHERE estado = :estado";
    }
    $sql .= " ORDER BY fecha_pedido DESC LIMIT :offset, :por_pagina";
    
    $stmt = $pdo->prepare($sql);
    if($filtro_estado) {
        $stmt->bindValue(':estado', $filtro_estado);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':por_pagina', $por_pagina, PDO::PARAM_INT);
    $stmt->execute();
    $pedidos = $stmt->fetchAll();
    
    // Total de pedidos para paginación
    $sql_count = "SELECT COUNT(*) as total FROM pedidos";
    if($filtro_estado) {
        $sql_count .= " WHERE estado = :estado";
        $stmt_count = $pdo->prepare($sql_count);
        $stmt_count->bindValue(':estado', $filtro_estado);
        $stmt_count->execute();
    } else {
        $stmt_count = $pdo->query($sql_count);
    }
    $total_registros = $stmt_count->fetch()['total'];
    $total_paginas = ceil($total_registros / $por_pagina);
    
} catch(PDOException $e) {
    $error = "Error al cargar pedidos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - THE PRINT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }
        .badge-estado {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .badge-estado:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .btn-action {
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>
                        Gestión de Pedidos
                    </h2>
                </div>
                <div>
                    <a href="dashboard.php" class="btn btn-light me-2">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                    <a href="../../index.php" class="btn btn-outline-light me-2">
                        <i class="bi bi-house-door me-1"></i>Sitio Web
                    </a>
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navegación -->
    <div class="container mb-4">
        <ul class="nav nav-pills nav-fill shadow-sm" style="background: white; border-radius: 10px; padding: 0.5rem;">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="pedidos.php">
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
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Lista de Pedidos
                </h5>
                
                <!-- Filtros -->
                <div class="btn-group" role="group">
                    <a href="?" class="btn btn-sm btn-outline-secondary <?php echo empty($filtro_estado) ? 'active' : ''; ?>">
                        Todos
                    </a>
                    <a href="?estado=pendiente" class="btn btn-sm btn-outline-warning <?php echo $filtro_estado == 'pendiente' ? 'active' : ''; ?>">
                        Pendientes
                    </a>
                    <a href="?estado=procesando" class="btn btn-sm btn-outline-info <?php echo $filtro_estado == 'procesando' ? 'active' : ''; ?>">
                        Procesando
                    </a>
                    <a href="?estado=completado" class="btn btn-sm btn-outline-success <?php echo $filtro_estado == 'completado' ? 'active' : ''; ?>">
                        Completados
                    </a>
                    <a href="?estado=cancelado" class="btn btn-sm btn-outline-danger <?php echo $filtro_estado == 'cancelado' ? 'active' : ''; ?>">
                        Cancelados
                    </a>
                </div>
            </div>
            
            <?php if(empty($pedidos)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">📦 No hay pedidos para mostrar</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#ID</th>
                                <th>Cliente</th>
                                <th>Email/Teléfono</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha/Hora</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pedidos as $pedido): ?>
                            <tr id="pedido-<?php echo $pedido['id']; ?>">
                                <td><strong>#<?php echo str_pad($pedido['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($pedido['nombre_cliente']); ?></td>
                                <td>
                                    <small>
                                        <?php echo htmlspecialchars($pedido['email_cliente']); ?><br>
                                        <?php echo htmlspecialchars($pedido['telefono']); ?>
                                    </small>
                                </td>
                                <td><strong>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></strong></td>
                                <td>
                                    <span class="badge badge-estado bg-<?php 
                                        echo $pedido['estado'] == 'pendiente' ? 'warning' : 
                                             ($pedido['estado'] == 'completado' ? 'success' : 
                                             ($pedido['estado'] == 'procesando' ? 'info' : 'danger')); 
                                    ?>" 
                                          onclick="abrirModalEstado(<?php echo $pedido['id']; ?>, '<?php echo $pedido['estado']; ?>')"
                                          title="Click para cambiar estado">
                                        <?php echo ucfirst($pedido['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?><br>
                                        <?php echo date('H:i', strtotime($pedido['fecha_pedido'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary btn-action" 
                                                onclick="verDetallesPedido(<?php echo $pedido['id']; ?>)" 
                                                title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-action" 
                                                onclick="confirmarEliminar(<?php echo $pedido['id']; ?>, '<?php echo htmlspecialchars($pedido['nombre_cliente']); ?>')" 
                                                title="Eliminar Pedido">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <?php if($total_paginas > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                            <a class="page-link" href="?pagina=<?php echo $i; ?><?php echo $filtro_estado ? '&estado='.$filtro_estado : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modales (igual que antes) -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>
                        Cambiar Estado del Pedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Pedido: <strong id="modal-pedido-id"></strong></p>
                    <div class="mb-3">
                        <label class="form-label">Seleccionar nuevo estado:</label>
                        <select class="form-select form-select-lg" id="nuevo-estado">
                            <option value="pendiente">⏳ Pendiente</option>
                            <option value="procesando">🔄 Procesando</option>
                            <option value="completado">✅ Completado</option>
                            <option value="cancelado">❌ Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="cambiarEstado()">
                        <i class="bi bi-check-circle me-1"></i>
                        Actualizar Estado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalles -->
    <div class="modal fade" id="modalDetallesPedido" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-receipt me-2"></i>
                        Detalles del Pedido
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalles-pedido-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Eliminar (SIN checkbox de restaurar stock) -->
    <div class="modal fade" id="modalEliminarPedido" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        Esta acción no se puede deshacer.
                    </div>
                    <p class="mb-3">¿Estás seguro de eliminar el pedido?</p>
                    <p class="mb-0">
                        <strong>Pedido:</strong> <span id="eliminar-pedido-id"></span><br>
                        <strong>Cliente:</strong> <span id="eliminar-pedido-cliente"></span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" onclick="eliminarPedido()">
                        <i class="bi bi-trash-fill me-1"></i>
                        Sí, Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let pedidoActualId = null;
        let pedidoEliminarId = null;
        const modalEstado = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
        const modalDetalles = new bootstrap.Modal(document.getElementById('modalDetallesPedido'));
        const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarPedido'));

        function abrirModalEstado(pedidoId, estadoActual) {
            pedidoActualId = pedidoId;
            document.getElementById('modal-pedido-id').textContent = '#' + String(pedidoId).padStart(5, '0');
            document.getElementById('nuevo-estado').value = estadoActual;
            modalEstado.show();
        }

        function cambiarEstado() {
            const nuevoEstado = document.getElementById('nuevo-estado').value;
            
            if (!pedidoActualId || !nuevoEstado) {
                alert('Error: Datos incompletos');
                return;
            }
            
            const formData = new FormData();
            formData.append('pedido_id', pedidoActualId);
            formData.append('estado', nuevoEstado);
            
            fetch('cambiar_estado.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fila = document.getElementById('pedido-' + pedidoActualId);
                    const badge = fila.querySelector('.badge-estado');
                    
                    badge.className = 'badge badge-estado bg-' + 
                        (nuevoEstado == 'pendiente' ? 'warning' : 
                         (nuevoEstado == 'completado' ? 'success' : 
                         (nuevoEstado == 'procesando' ? 'info' : 'danger')));
                    badge.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
                    badge.onclick = function() { abrirModalEstado(pedidoActualId, nuevoEstado); };
                    
                    modalEstado.hide();
                    mostrarAlerta('✅ Estado actualizado exitosamente', 'success');
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al actualizar el estado');
            });
        }

        function verDetallesPedido(pedidoId) {
            modalDetalles.show();
            
            fetch('ver_pedido.php?id=' + pedidoId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const pedido = data.pedido;
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Información del Cliente
                                </h6>
                                <p class="mb-2"><strong>Nombre:</strong> ${pedido.nombre_cliente}</p>
                                <p class="mb-2"><strong>Email:</strong> ${pedido.email_cliente}</p>
                                <p class="mb-2"><strong>Teléfono:</strong> ${pedido.telefono}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bi bi-receipt me-2"></i>
                                    Información del Pedido
                                </h6>
                                <p class="mb-2"><strong>ID:</strong> #${String(pedido.id).padStart(5, '0')}</p>
                                <p class="mb-2"><strong>Fecha:</strong> ${new Date(pedido.fecha_pedido).toLocaleString('es-CO')}</p>
                                <p class="mb-2"><strong>Estado:</strong> 
                                    <span class="badge bg-${pedido.estado == 'pendiente' ? 'warning' : (pedido.estado == 'completado' ? 'success' : (pedido.estado == 'procesando' ? 'info' : 'danger'))}">
                                        ${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1)}
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-box-seam me-2"></i>
                            Productos Ordenados
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    
                    pedido.productos_array.forEach(item => {
                        html += `
                            <tr>
                                <td>${item.nombre}</td>
                                <td class="text-center">${item.cantidad}</td>
                                <td class="text-end">$${Number(item.precio).toLocaleString('es-CO')}</td>
                                <td class="text-end"><strong>$${Number(item.subtotal).toLocaleString('es-CO')}</strong></td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                        <td class="text-end"><h5 class="mb-0 text-success">$${Number(pedido.total).toLocaleString('es-CO')}</h5></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <hr>
                        
                        <h6 class="text-primary mb-3">
                            <i class="bi bi-geo-alt me-2"></i>
                            Dirección de Envío
                        </h6>
                        <p class="mb-0">${pedido.direccion_envio.replace(/\n/g, '<br>')}</p>
                    `;
                    
                    if (pedido.notas) {
                        html += `
                            <hr>
                            <h6 class="text-primary mb-3">
                                <i class="bi bi-sticky me-2"></i>
                                Notas del Cliente
                            </h6>
                            <p class="mb-0">${pedido.notas.replace(/\n/g, '<br>')}</p>
                        `;
                    }
                    
                    document.getElementById('detalles-pedido-content').innerHTML = html;
                } else {
                    document.getElementById('detalles-pedido-content').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${data.message}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('detalles-pedido-content').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Error al cargar los detalles del pedido
                    </div>
                `;
            });
        }

        function confirmarEliminar(pedidoId, nombreCliente) {
            pedidoEliminarId = pedidoId;
            document.getElementById('eliminar-pedido-id').textContent = '#' + String(pedidoId).padStart(5, '0');
            document.getElementById('eliminar-pedido-cliente').textContent = nombreCliente;
            modalEliminar.show();
        }

        function eliminarPedido() {
            if (!pedidoEliminarId) {
                alert('Error: ID de pedido no válido');
                return;
            }
            
            const formData = new FormData();
            formData.append('pedido_id', pedidoEliminarId);
            formData.append('restaurar_stock', 'false'); // Siempre false
            
            fetch('eliminar_pedido.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fila = document.getElementById('pedido-' + pedidoEliminarId);
                    fila.style.transition = 'all 0.5s ease';
                    fila.style.opacity = '0';
                    fila.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        fila.remove();
                        modalEliminar.hide();
                        mostrarAlerta('✅ Pedido eliminado exitosamente', 'success');
                        
                        if (document.querySelectorAll('tbody tr').length === 0) {
                            location.reload();
                        }
                    }, 500);
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error al eliminar el pedido');
            });
        }

        function mostrarAlerta(mensaje, tipo) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
    </script>
</body>
</html>
