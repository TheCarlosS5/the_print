<?php
/**
 * Archivo: datos_productos.php
 * Descripción: Carga productos desde la base de datos o usa datos de ejemplo
 */

// Asegurarse de que config.php ya está incluido
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

// Array para almacenar todos los productos
$todos_los_productos = [];

try {
    // Conexión a la base de datos
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si existe la tabla productos
    $tabla_existe = $pdo->query("SHOW TABLES LIKE 'productos'")->rowCount() > 0;
    
    if ($tabla_existe) {
        // Cargar productos desde la BD
        $stmt = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY tipo, nombre");
        $productos_bd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convertir a formato de array asociativo
        foreach ($productos_bd as $producto) {
            $todos_los_productos[$producto['id']] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'descripcion' => $producto['descripcion'] ?? '',
                'precio' => (float)$producto['precio'],
                'tipo' => $producto['tipo'] ?? 'general',
                'img' => !empty($producto['imagen']) ? BASE_URL . $producto['imagen'] : BASE_URL . 'assets/img/default.jpg',
                'stock' => (int)$producto['stock']
            ];
        }
    }
    
} catch (PDOException $e) {
    // Si hay error, usar productos de ejemplo
    error_log("Error al cargar productos: " . $e->getMessage());
}

// Si no hay productos en BD, usar productos de ejemplo (los de tu código original)
if (empty($todos_los_productos)) {
    $todos_los_productos = [
        // Impresoras
        'imp001' => [
            'id' => 'imp001',
            'nombre' => 'Impresora Multifuncional Epson L3250',
            'descripcion' => 'Sistema continuo de tinta, WiFi, escáner y copiadora',
            'precio' => 850000,
            'tipo' => 'impresoras',
            'img' => BASE_URL . 'assets/img/Epson L3250.png',
            'stock' => 15
        ],
        'imp002' => [
            'id' => 'imp002',
            'nombre' => 'Impresora Láser HP 107w',
            'descripcion' => 'Impresora láser monocromática con WiFi',
            'precio' => 620000,
            'tipo' => 'impresoras',
            'img' => BASE_URL . 'assets/img/HP 107w.avif',
            'stock' => 20
        ],
        'imp003' => [
            'id' => 'imp003',
            'nombre' => 'Impresora Canon G3110',
            'descripcion' => 'Sistema de tanques de tinta recargables',
            'precio' => 790000,
            'tipo' => 'impresoras',
            'img' => BASE_URL . 'assets/img/impr1.jpg',
            'stock' => 12
        ],
        // Consumibles
        'cons001' => [
            'id' => 'cons001',
            'nombre' => 'Cartucho Tinta Negra HP 664',
            'descripcion' => 'Cartucho original HP 664 negro',
            'precio' => 55000,
            'tipo' => 'consumibles',
            'img' => BASE_URL . 'assets/img/tintasepson.jpg',
            'stock' => 50
        ],
        'cons002' => [
            'id' => 'cons002',
            'nombre' => 'Tóner HP 105A para Láser',
            'descripcion' => 'Tóner original HP 105A negro',
            'precio' => 210000,
            'tipo' => 'consumibles',
            'img' => BASE_URL . 'assets/img/Toner HP 105A para Láser.avif',
            'stock' => 30
        ],
        // Papelería
        'papel001' => [
            'id' => 'papel001',
            'nombre' => 'Resma de Papel Carta 75g (Caja x10)',
            'descripcion' => 'Caja con 10 resmas papel bond carta 75g',
            'precio' => 280000,
            'tipo' => 'papeleria',
            'img' => BASE_URL . 'assets/img/Papel Fotográfico Brillante A4 x20.png',
            'stock' => 25
        ],
        'papel002' => [
            'id' => 'papel002',
            'nombre' => 'Papel Fotográfico Brillante A4 x20',
            'descripcion' => 'Papel fotográfico glossy 180gr, 20 hojas A4',
            'precio' => 15000,
            'tipo' => 'papeleria',
            'img' => BASE_URL . 'assets/img/Papel Fotográfico Brillante A4 x20.png',
            'stock' => 60
        ],
        // Repuestos
        'rep001' => [
            'id' => 'rep001',
            'nombre' => 'Cable USB para Impresora 1.8m',
            'descripcion' => 'Cable USB tipo A a tipo B',
            'precio' => 12000,
            'tipo' => 'repuestos',
            'img' => BASE_URL . 'assets/img/repuestos.jpg',
            'stock' => 100
        ],
        'rep002' => [
            'id' => 'rep002',
            'nombre' => 'Kit Mantenimiento Epson',
            'descripcion' => 'Kit completo de mantenimiento Epson serie L',
            'precio' => 70000,
            'tipo' => 'repuestos',
            'img' => BASE_URL . 'assets/img/Kit Mantenimiento Epson.webp',
            'stock' => 18
        ]
    ];
}

/**
 * Función para obtener un producto por su ID
 */
function obtener_producto($id) {
    global $todos_los_productos;
    return $todos_los_productos[$id] ?? null;
}

/**
 * Función para obtener productos por tipo
 */
function obtener_productos_por_tipo($tipo) {
    global $todos_los_productos;
    
    if ($tipo === 'todos') {
        return $todos_los_productos;
    }
    
    return array_filter($todos_los_productos, function($producto) use ($tipo) {
        return $producto['tipo'] === $tipo;
    });
}

/**
 * Función para obtener todos los tipos de productos disponibles
 */
function obtener_tipos_productos() {
    global $todos_los_productos;
    
    $tipos = [];
    foreach ($todos_los_productos as $producto) {
        if (!isset($tipos[$producto['tipo']])) {
            $tipos[$producto['tipo']] = ucfirst($producto['tipo']);
        }
    }
    
    return $tipos;
}

/**
 * Función para calcular el total del carrito
 */
function calcular_total_carrito($carrito) {
    global $todos_los_productos;
    
    $total = 0;
    foreach ($carrito as $id_producto => $cantidad) {
        if (isset($todos_los_productos[$id_producto])) {
            $total += $todos_los_productos[$id_producto]['precio'] * $cantidad;
        }
    }
    
    return $total;
}

/**
 * Función para contar items en el carrito
 */
function contar_items_carrito() {
    if (!isset($_SESSION['carrito'])) {
        return 0;
    }
    
    return array_sum($_SESSION['carrito']);
}

/**
 * Función para buscar productos por nombre
 */
function buscar_productos($termino) {
    global $todos_los_productos;
    
    $termino = strtolower($termino);
    
    return array_filter($todos_los_productos, function($producto) use ($termino) {
        return strpos(strtolower($producto['nombre']), $termino) !== false ||
               strpos(strtolower($producto['descripcion']), $termino) !== false;
    });
}
?>
