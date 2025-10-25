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

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $pedido_id = (int)$_GET['id'];
    
    try {
        // Obtener pedido
        $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = :id");
        $stmt->execute([':id' => $pedido_id]);
        $pedido = $stmt->fetch();
        
        if ($pedido) {
            // Decodificar productos JSON
            $pedido['productos_array'] = json_decode($pedido['productos'], true);
            
            echo json_encode(['success' => true, 'pedido' => $pedido]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de pedido no proporcionado']);
}
?>
