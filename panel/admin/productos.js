const modalProducto = new bootstrap.Modal(document.getElementById('modalProducto'));

// Abrir modal para agregar producto
function abrirModalAgregar() {
    document.getElementById('modalProductoTitulo').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Agregar Producto';
    document.getElementById('producto-accion').value = 'agregar';
    document.getElementById('formProducto').reset();
    document.getElementById('producto-activo').checked = true;
    document.getElementById('producto-id').readOnly = false;
    modalProducto.show();
}

// Abrir modal para editar producto
function editarProducto(producto) {
    document.getElementById('modalProductoTitulo').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Editar Producto';
    document.getElementById('producto-accion').value = 'editar';
    document.getElementById('producto-id-original').value = producto.id;
    
    // Llenar formulario con datos del producto
    document.getElementById('producto-id').value = producto.id;
    document.getElementById('producto-id').readOnly = true;
    document.getElementById('producto-nombre').value = producto.nombre;
    document.getElementById('producto-descripcion').value = producto.descripcion || '';
    document.getElementById('producto-tipo').value = producto.tipo;
    document.getElementById('producto-precio').value = producto.precio;
    document.getElementById('producto-stock').value = producto.stock;
    document.getElementById('producto-imagen').value = producto.imagen || '';
    document.getElementById('producto-activo').checked = producto.activo == 1;
    
    modalProducto.show();
}

// Guardar producto (agregar o editar)
document.getElementById('formProducto').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const accion = document.getElementById('producto-accion').value;
    const formData = new FormData();
    
    formData.append('action', accion);
    formData.append('id', document.getElementById('producto-id').value);
    formData.append('nombre', document.getElementById('producto-nombre').value);
    formData.append('descripcion', document.getElementById('producto-descripcion').value);
    formData.append('tipo', document.getElementById('producto-tipo').value);
    formData.append('precio', document.getElementById('producto-precio').value);
    formData.append('stock', document.getElementById('producto-stock').value);
    formData.append('imagen', document.getElementById('producto-imagen').value);
    formData.append('activo', document.getElementById('producto-activo').checked ? 1 : 0);
    
    if (accion === 'editar') {
        formData.append('id_original', document.getElementById('producto-id-original').value);
    }
    
    fetch('productos_acciones.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
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
            // Eliminar fila con animación
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
