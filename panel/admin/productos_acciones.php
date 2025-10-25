<?php
// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if(!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$action = $_POST['action'] ?? '';

// ========== FUNCIÓN: GENERAR ID AUTOMÁTICO ==========
function generarIdProducto($pdo, $tipo) {
    // Prefijos por tipo
    $prefijos = [
        'impresoras' => 'IMP',
        'consumibles' => 'CONS',
        'papeleria' => 'PAP',
        'repuestos' => 'REP'
    ];
    
    $prefijo = $prefijos[$tipo] ?? 'PROD';
    
    // Buscar el último ID con ese prefijo
    $stmt = $pdo->prepare("SELECT id FROM productos WHERE id LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$prefijo . '%']);
    $ultimo = $stmt->fetch();
    
    if ($ultimo) {
        // Extraer el número y sumar 1
        $numero = (int)substr($ultimo['id'], strlen($prefijo)) + 1;
    } else {
        $numero = 1;
    }
    
    // Formatear con ceros a la izquierda (3 dígitos)
    return $prefijo . str_pad($numero, 3, '0', STR_PAD_LEFT);
}

// ========== FUNCIÓN: SUBIR IMAGEN ==========
function subirImagen($archivo, $imagen_anterior = null) {
    // Si no hay archivo nuevo, retornar la imagen anterior
    if (!isset($archivo) || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'ruta' => $imagen_anterior];
    }
    
    // Verificar errores de subida
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error al subir el archivo'];
    }
    
    // Validar tamaño (5MB máximo)
    if ($archivo['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'La imagen no debe superar 5MB'];
    }
    
    // Validar tipo MIME
    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $archivo['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $tipos_permitidos)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF, WEBP, AVIF'];
    }
    
    // Obtener extensión
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    
    // Generar nombre único
    $nombre_archivo = 'producto_' . uniqid() . '_' . time() . '.' . $extension;
    
    // Ruta de destino
    $directorio_destino = __DIR__ . '/../../assets/img/';
    $ruta_completa = $directorio_destino . $nombre_archivo;
    
    // Crear directorio si no existe
    if (!file_exists($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        // Eliminar imagen anterior si existe y es diferente
        if ($imagen_anterior && $imagen_anterior !== 'assets/img/default.jpg') {
            $ruta_anterior = __DIR__ . '/../../' . $imagen_anterior;
            if (file_exists($ruta_anterior)) {
                @unlink($ruta_anterior);
            }
        }
        
        return ['success' => true, 'ruta' => 'assets/img/' . $nombre_archivo];
    } else {
        return ['success' => false, 'message' => 'Error al mover el archivo'];
    }
}

// ========== AGREGAR PRODUCTO ==========
if ($action === 'agregar') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $activo = isset($_POST['activo']) && $_POST['activo'] === 'on' ? 1 : 0;
    
    // Validaciones
    if (empty($nombre) || empty($tipo)) {
        echo json_encode(['success' => false, 'message' => 'Nombre y tipo son obligatorios']);
        exit;
    }
    
    if ($precio < 0) {
        echo json_encode(['success' => false, 'message' => 'El precio no puede ser negativo']);
        exit;
    }
    
    if ($stock < 0) {
        echo json_encode(['success' => false, 'message' => 'El stock no puede ser negativo']);
        exit;
    }
    
    try {
        // Generar ID automático
        $id = generarIdProducto($pdo, $tipo);
        
        // Subir imagen
        $resultado_imagen = subirImagen($_FILES['imagen'] ?? null);
        if (!$resultado_imagen['success']) {
            echo json_encode(['success' => false, 'message' => $resultado_imagen['message']]);
            exit;
        }
        
        $ruta_imagen = $resultado_imagen['ruta'] ?? 'assets/img/default.jpg';
        
        // Insertar producto
        $sql = "INSERT INTO productos (id, nombre, descripcion, tipo, precio, stock, imagen, activo, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $nombre, $descripcion, $tipo, $precio, $stock, $ruta_imagen, $activo]);
        
        error_log("[ADMIN] Producto '{$id}' agregado por " . ($_SESSION['admin_email'] ?? 'admin'));
        
        echo json_encode([
            'success' => true, 
            'message' => 'Producto agregado exitosamente con ID: ' . $id,
            'id' => $id
        ]);
        
    } catch (PDOException $e) {
        error_log("[ERROR] Error al agregar producto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
}

// ========== EDITAR PRODUCTO ==========
elseif ($action === 'editar') {
    $id_original = trim($_POST['id_original'] ?? '');
    $id = trim($_POST['id'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $activo = isset($_POST['activo']) && $_POST['activo'] === 'on' ? 1 : 0;
    $imagen_actual = trim($_POST['imagen_actual'] ?? '');
    
    // Validaciones
    if (empty($id_original) || empty($id) || empty($nombre) || empty($tipo)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
        exit;
    }
    
    if ($precio < 0 || $stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Precio y stock no pueden ser negativos']);
        exit;
    }
    
    try {
        // Verificar si el producto existe
        $stmt_check = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt_check->execute([$id_original]);
        $producto_existente = $stmt_check->fetch();
        
        if (!$producto_existente) {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit;
        }
        
        // Procesar imagen (mantener la actual si no hay nueva)
        $imagen_anterior = $producto_existente['imagen'];
        $resultado_imagen = subirImagen($_FILES['imagen'] ?? null, $imagen_anterior);
        
        if (!$resultado_imagen['success']) {
            echo json_encode(['success' => false, 'message' => $resultado_imagen['message']]);
            exit;
        }
        
        $ruta_imagen = $resultado_imagen['ruta'];
        
        // Actualizar producto
        $sql = "UPDATE productos SET 
                id = ?, nombre = ?, descripcion = ?, tipo = ?, 
                precio = ?, stock = ?, imagen = ?, activo = ?
                WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $nombre, $descripcion, $tipo, $precio, $stock, $ruta_imagen, $activo, $id_original]);
        
        error_log("[ADMIN] Producto '{$id_original}' editado por " . ($_SESSION['admin_email'] ?? 'admin'));
        
        echo json_encode(['success' => true, 'message' => 'Producto actualizado exitosamente']);
        
    } catch (PDOException $e) {
        error_log("[ERROR] Error al editar producto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
}

// ========== ELIMINAR PRODUCTO ==========
elseif ($action === 'eliminar') {
    $id = trim($_POST['id'] ?? '');
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de producto no especificado']);
        exit;
    }
    
    try {
        // Obtener imagen antes de eliminar
        $stmt_check = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt_check->execute([$id]);
        $producto = $stmt_check->fetch();
        
        if (!$producto) {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit;
        }
        
        // Eliminar producto de la BD
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        
        // Eliminar imagen del servidor
        if ($producto['imagen'] && $producto['imagen'] !== 'assets/img/default.jpg') {
            $ruta_imagen = __DIR__ . '/../../' . $producto['imagen'];
            if (file_exists($ruta_imagen)) {
                @unlink($ruta_imagen);
            }
        }
        
        error_log("[ADMIN] Producto '{$id}' eliminado por " . ($_SESSION['admin_email'] ?? 'admin'));
        
        echo json_encode(['success' => true, 'message' => 'Producto eliminado exitosamente']);
        
    } catch (PDOException $e) {
        error_log("[ERROR] Error al eliminar producto: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
}
?>
