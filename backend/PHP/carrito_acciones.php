<?php
/**
 * Archivo: carrito_acciones.php
 * Descripción: Maneja todas las acciones del carrito de compras
 */

require_once __DIR__ . '/config.php';

// Verificar que se recibió una acción
if (!isset($_POST['action'])) {
    header('Location: ' . BASE_URL);
    exit();
}

$action = $_POST['action'];

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// ========== AGREGAR PRODUCTO AL CARRITO ==========
if ($action === 'agregar') {
    $id_producto = $_POST['id_producto'] ?? null;
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
    
    if ($id_producto && $cantidad > 0) {
        // Si el producto ya existe en el carrito, sumamos la cantidad
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto] += $cantidad;
        } else {
            // Si no existe, lo agregamos
            $_SESSION['carrito'][$id_producto] = $cantidad;
        }
        
        $_SESSION['mensaje_carrito'] = [
            'tipo' => 'success',
            'texto' => 'Producto agregado al carrito exitosamente'
        ];
    } else {
        $_SESSION['mensaje_carrito'] = [
            'tipo' => 'danger',
            'texto' => 'Error al agregar el producto'
        ];
    }
    
    // Redirigir de vuelta a la página de productos
    $redirect = $_POST['redirect'] ?? BASE_URL . 'backend/productos/productos.php';
    header('Location: ' . $redirect);
    exit();
}

// ========== ACTUALIZAR CANTIDAD ==========
if ($action === 'actualizar') {
    $id_producto = $_POST['id_producto'] ?? null;
    $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
    
    if ($id_producto) {
        if ($cantidad > 0) {
            // Actualizar la cantidad
            $_SESSION['carrito'][$id_producto] = $cantidad;
            $_SESSION['mensaje_carrito'] = [
                'tipo' => 'success',
                'texto' => 'Cantidad actualizada'
            ];
        } else {
            // Si la cantidad es 0, eliminar el producto
            unset($_SESSION['carrito'][$id_producto]);
            $_SESSION['mensaje_carrito'] = [
                'tipo' => 'info',
                'texto' => 'Producto eliminado del carrito'
            ];
        }
    }
    
    // Redirigir al carrito
    header('Location: ' . BASE_URL . 'backend/pedidos/carrito.php');
    exit();
}

// ========== ELIMINAR PRODUCTO ==========
if ($action === 'eliminar') {
    $id_producto = $_POST['id_producto'] ?? null;
    
    if ($id_producto && isset($_SESSION['carrito'][$id_producto])) {
        unset($_SESSION['carrito'][$id_producto]);
        $_SESSION['mensaje_carrito'] = [
            'tipo' => 'success',
            'texto' => 'Producto eliminado del carrito'
        ];
    }
    
    // Redirigir al carrito
    header('Location: ' . BASE_URL . 'backend/pedidos/carrito.php');
    exit();
}

// ========== VACIAR CARRITO ==========
if ($action === 'vaciar') {
    $_SESSION['carrito'] = [];
    $_SESSION['mensaje_carrito'] = [
        'tipo' => 'info',
        'texto' => 'Carrito vaciado'
    ];
    
    // Redirigir al carrito
    header('Location: ' . BASE_URL . 'backend/pedidos/carrito.php');
    exit();
}

// Si no se reconoce la acción, redirigir al inicio
header('Location: ' . BASE_URL);
exit();
?>
