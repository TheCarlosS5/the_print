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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : 0;
    $nuevo_estado = isset($_POST['estado']) ? trim($_POST['estado']) : '';
    
    // Validar estados permitidos
    $estados_validos = ['pendiente', 'procesando', 'completado', 'cancelado'];
    
    if ($pedido_id <= 0 || !in_array($nuevo_estado, $estados_validos)) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    try {
        // Actualizar estado
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = :estado WHERE id = :id");
        $stmt->execute([
            ':estado' => $nuevo_estado,
            ':id' => $pedido_id
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true, 
                'message' => 'Estado actualizado exitosamente',
                'nuevo_estado' => $nuevo_estado
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
