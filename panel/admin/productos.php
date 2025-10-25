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

// Obtener productos
try {
    $stmt = $pdo->query("SELECT * FROM productos ORDER BY tipo, nombre");
    $productos = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error al cargar productos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - THE PRINT</title>
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
        .producto-img-small {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            margin-top: 10px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 5px;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        .upload-area.drag-over {
            border-color: #667eea;
            background: #e8f0fe;
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
                        <i class="bi bi-box-seam me-2"></i>
                        Gestión de Productos
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

    <!-- Navegación por pestañas -->
    <div class="container mb-4">
        <ul class="nav nav-pills nav-fill shadow-sm" style="background: white; border-radius: 10px; padding: 0.5rem;">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="pedidos.php">
                    <i class="bi bi-receipt me-2"></i>Pedidos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="productos.php">
                    <i class="bi bi-box-seam me-2"></i>Productos
                </a>
            </li>
        </ul>
    </div>

    <div class="container">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    Lista de Productos
                </h5>
                <button class="btn btn-primary" onclick="abrirModalAgregar()">
                    <i class="bi bi-plus-circle me-1"></i>
                    Agregar Producto
                </button>
            </div>

            <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Imagen</th>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productos as $prod): ?>
                        <tr id="producto-<?php echo $prod['id']; ?>">
                            <td>
                                <img src="../../<?php echo $prod['imagen']; ?>" 
                                     class="producto-img-small" 
                                     alt="<?php echo htmlspecialchars($prod['nombre']); ?>"
                                     onerror="this.src='../../assets/img/default.jpg'">
                            </td>
                            <td><code><?php echo htmlspecialchars($prod['id']); ?></code></td>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td><span class="badge bg-secondary"><?php echo ucfirst($prod['tipo']); ?></span></td>
                            <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $prod['stock'] > 10 ? 'success' : ($prod['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                    <?php echo $prod['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $prod['activo'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $prod['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick='editarProducto(<?php echo json_encode($prod); ?>)' title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="eliminarProducto('<?php echo $prod['id']; ?>', '<?php echo htmlspecialchars($prod['nombre']); ?>')" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal: Agregar/Editar Producto -->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProductoTitulo">
                        <i class="bi bi-plus-circle me-2"></i>
                        Agregar Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formProducto" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="producto-accion" value="agregar">
                        <input type="hidden" id="producto-id" name="id">
                        <input type="hidden" id="producto-id-original">
                        <input type="hidden" id="imagen-actual" name="imagen_actual">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo *</label>
                                <select class="form-select" id="producto-tipo" name="tipo" required>
                                    <option value="impresoras">Impresoras</option>
                                    <option value="consumibles">Consumibles</option>
                                    <option value="papeleria">Papelería</option>
                                    <option value="repuestos">Repuestos</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="producto-activo" name="activo" checked>
                                    <label class="form-check-label" for="producto-activo">
                                        Producto activo (visible en tienda)
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre del Producto *</label>
                            <input type="text" class="form-control" id="producto-nombre" name="nombre" required maxlength="200">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" id="producto-descripcion" name="descripcion" rows="3" maxlength="500"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="producto-precio" name="precio" required min="0" step="100">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock *</label>
                                <input type="number" class="form-control" id="producto-stock" name="stock" required min="0">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Imagen del Producto</label>
                            <div class="upload-area" id="uploadArea">
                                <i class="bi bi-cloud-upload fs-1 text-muted"></i>
                                <p class="mb-2">Arrastra una imagen aquí o haz clic para seleccionar</p>
                                <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP, AVIF (Máx: 5MB)</small>
                                <input type="file" class="d-none" id="producto-imagen" name="imagen" accept="image/*">
                            </div>
                            <div id="preview-container" class="text-center" style="display: none;">
                                <img id="image-preview" class="preview-image" src="" alt="Vista previa">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="eliminarImagen()">
                                        <i class="bi bi-trash me-1"></i>Eliminar imagen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="bi bi-save me-1"></i>
                            <span id="btnGuardarTexto">Guardar Producto</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalProducto = new bootstrap.Modal(document.getElementById('modalProducto'));
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('producto-imagen');
        const previewContainer = document.getElementById('preview-container');
        const imagePreview = document.getElementById('image-preview');

        // Click en área de upload
        uploadArea.addEventListener('click', () => fileInput.click());

        // Prevenir comportamiento por defecto del drag & drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight al arrastrar
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.classList.add('drag-over'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('drag-over'), false);
        });

        // Handle drop
        uploadArea.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            handleFiles(files);
        });

        // Handle file select
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                const file = files[0];
                
                // Validar tipo de archivo
                if (!file.type.startsWith('image/')) {
                    alert('Por favor selecciona un archivo de imagen válido');
                    return;
                }
                
                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen no debe superar 5MB');
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    uploadArea.style.display = 'none';
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function eliminarImagen() {
            fileInput.value = '';
            uploadArea.style.display = 'block';
            previewContainer.style.display = 'none';
            imagePreview.src = '';
        }

        // Abrir modal para agregar producto
        function abrirModalAgregar() {
            document.getElementById('modalProductoTitulo').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Agregar Producto';
            document.getElementById('producto-accion').value = 'agregar';
            document.getElementById('formProducto').reset();
            document.getElementById('producto-activo').checked = true;
            eliminarImagen();
            modalProducto.show();
        }

        // Abrir modal para editar producto
        function editarProducto(producto) {
            document.getElementById('modalProductoTitulo').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Producto';
            document.getElementById('producto-accion').value = 'editar';
            document.getElementById('producto-id-original').value = producto.id;
            document.getElementById('producto-id').value = producto.id;
            
            // Llenar formulario
            document.getElementById('producto-nombre').value = producto.nombre;
            document.getElementById('producto-descripcion').value = producto.descripcion || '';
            document.getElementById('producto-tipo').value = producto.tipo;
            document.getElementById('producto-precio').value = producto.precio;
            document.getElementById('producto-stock').value = producto.stock;
            document.getElementById('producto-activo').checked = producto.activo == 1;
            document.getElementById('imagen-actual').value = producto.imagen || '';
            
            // Mostrar imagen actual si existe
            if (producto.imagen) {
                imagePreview.src = '../../' + producto.imagen;
                uploadArea.style.display = 'none';
                previewContainer.style.display = 'block';
            } else {
                eliminarImagen();
            }
            
            modalProducto.show();
        }

        // Guardar producto
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btnGuardar = document.getElementById('btnGuardar');
            const btnTexto = document.getElementById('btnGuardarTexto');
            
            // Deshabilitar botón
            btnGuardar.disabled = true;
            btnTexto.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
            
            const formData = new FormData(this);
            formData.append('action', document.getElementById('producto-accion').value);
            
            if (document.getElementById('producto-accion').value === 'editar') {
                formData.append('id_original', document.getElementById('producto-id-original').value);
            }
            
            fetch('productos_acciones.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnGuardar.disabled = false;
                btnTexto.textContent = 'Guardar Producto';
                
                if (data.success) {
                    modalProducto.hide();
                    mostrarAlerta(data.message, 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    mostrarAlerta('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btnGuardar.disabled = false;
                btnTexto.textContent = 'Guardar Producto';
                mostrarAlerta('Error al procesar la solicitud', 'danger');
            });
        });

        // Eliminar producto
        function eliminarProducto(id, nombre) {
            if (!confirm(`¿Estás seguro de eliminar el producto:\n"${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id', id);
            
            fetch('productos_acciones.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const fila = document.getElementById('producto-' + id);
                    fila.style.transition = 'all 0.5s ease';
                    fila.style.opacity = '0';
                    fila.style.transform = 'translateX(-100%)';
                    
                    setTimeout(() => {
                        fila.remove();
                        mostrarAlerta(data.message, 'success');
                    }, 500);
                } else {
                    mostrarAlerta('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error al eliminar el producto', 'danger');
            });
        }

        // Mostrar alertas
        function mostrarAlerta(mensaje, tipo) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '400px';
            alertDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 4000);
        }
    </script>

</body>
</html>
