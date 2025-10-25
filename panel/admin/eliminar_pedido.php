<?php
// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if(!isset($_SESSION['admin_autenticado']) || $_SESSION['admin_autenticado'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Incluir configuración de base de datos
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : 0;
    $restaurar_stock = isset($_POST['restaurar_stock']) && $_POST['restaurar_stock'] === 'true';
    
    if ($pedido_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de pedido inválido']);
        exit;
    }
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Obtener datos del pedido antes de eliminar
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
        $stmt->execute([$pedido_id]);
        $pedido = $stmt->fetch();
        
        if (!$pedido) {
            throw new Exception('Pedido no encontrado');
        }
        
        // Si se debe restaurar el stock
        if ($restaurar_stock) {
            $productos = json_decode($pedido['productos'], true);
            
            if (is_array($productos)) {
                // Verificar si existe la tabla productos
                $tabla_productos_existe = $pdo->query("SHOW TABLES LIKE 'productos'")->rowCount() > 0;
                
                if ($tabla_productos_existe) {
                    $sql_stock = "UPDATE productos SET stock = stock + ? WHERE id = ?";
                    $stmt_stock = $pdo->prepare($sql_stock);
                    
                    foreach ($productos as $item) {
                        if (isset($item['id']) && isset($item['cantidad'])) {
                            $stmt_stock->execute([
                                $item['cantidad'],
                                $item['id']
                            ]);
                        }
                    }
                }
            }
        }
        
        // Eliminar items del pedido primero (si existen)
        $tabla_items_existe = $pdo->query("SHOW TABLES LIKE 'pedido_items'")->rowCount() > 0;
        if ($tabla_items_existe) {
            $stmt_items = $pdo->prepare("DELETE FROM pedido_items WHERE pedido_id = ?");
            $stmt_items->execute([$pedido_id]);
        }
        
        // Eliminar el pedido
        $stmt_delete = $pdo->prepare("DELETE FROM pedidos WHERE id = ?");
        $stmt_delete->execute([$pedido_id]);
        
        // Confirmar transacción
        $pdo->commit();
        
        // Registrar en log
        error_log("[ADMIN] Pedido #{$pedido_id} eliminado por " . ($_SESSION['admin_email'] ?? 'admin'));
        
        echo json_encode([
            'success' => true, 
            'message' => 'Pedido eliminado exitosamente',
            'stock_restaurado' => $restaurar_stock
        ]);
        
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("[ERROR] Error al eliminar pedido: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
        
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
