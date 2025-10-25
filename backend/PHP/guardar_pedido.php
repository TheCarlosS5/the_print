<?php
/**
 * Archivo: guardar_pedido.php
 * Descripción: Procesa pedidos con validaciones de seguridad
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/datos_productos.php';

// Incluir PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';

// ========== VERIFICACIONES DE SEGURIDAD ==========

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_security_event('PEDIDO', 'Intento de acceso con método incorrecto: ' . $_SERVER['REQUEST_METHOD'], 'WARNING');
    header('Location: ' . BASE_URL . 'backend/pedidos/carrito.php');
    exit();
}

// Verificar CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    log_security_event('PEDIDO', 'Token CSRF inválido', 'WARNING');
    $_SESSION['error_pedido'] = "Sesión inválida. Por favor, recarga la página.";
    header('Location: ' . BASE_URL . 'checkout.php');
    exit();
}

// Verificar rate limiting
if (!check_rate_limit('pedido', 3, 60)) {
    log_security_event('PEDIDO', 'Rate limit excedido', 'WARNING');
    $_SESSION['error_pedido'] = "Demasiados intentos. Por favor, espera un momento.";
    header('Location: ' . BASE_URL . 'checkout.php');
    exit();
}

// Verificar que hay productos en el carrito
if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
    $_SESSION['error_pedido'] = "Tu carrito está vacío.";
    header('Location: ' . BASE_URL . 'backend/pedidos/carrito.php');
    exit();
}

// ========== VALIDACIÓN Y SANITIZACIÓN DE DATOS ==========

// Limpiar y validar datos del formulario
$nombre_cliente = clean_input($_POST['nombre'] ?? '');
$email_cliente = clean_input($_POST['email'] ?? '');
$telefono = clean_input($_POST['telefono'] ?? '');
$direccion_envio = clean_input($_POST['direccion'] ?? '');
$notas = clean_input($_POST['notas'] ?? '');

// Array para almacenar errores de validación
$errores = [];

// Validar nombre (2-100 caracteres)
if (!validate_length($nombre_cliente, 2, 100)) {
    $errores[] = "El nombre debe tener entre 2 y 100 caracteres.";
}

// Validar email
if (!validate_email($email_cliente)) {
    $errores[] = "El correo electrónico no es válido.";
}

// Validar teléfono
if (!validate_phone($telefono)) {
    $errores[] = "El teléfono debe ser un número colombiano válido (10 dígitos).";
}

// Validar dirección (10-500 caracteres)
if (!validate_length($direccion_envio, 10, 500)) {
    $errores[] = "La dirección debe tener entre 10 y 500 caracteres.";
}

// Validar notas (opcional, máximo 1000 caracteres)
if (!empty($notas) && !validate_length($notas, 0, 1000)) {
    $errores[] = "Las notas no pueden exceder 1000 caracteres.";
}

// Si hay errores, retornar
if (!empty($errores)) {
    $_SESSION['error_pedido'] = implode(' ', $errores);
    log_security_event('PEDIDO', 'Validación fallida: ' . implode(', ', $errores), 'WARNING');
    header('Location: ' . BASE_URL . 'checkout.php');
    exit();
}

try {
    // Obtener conexión segura
    $pdo = obtener_conexion();
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Calcular y validar productos
    $total_pedido = 0;
    $productos_json = [];
    
    foreach ($_SESSION['carrito'] as $id_producto => $cantidad) {
        // Validar ID de producto
        $id_producto = clean_input($id_producto);
        
        // Validar cantidad
        if (!validate_positive_int($cantidad) || $cantidad > 100) {
            throw new Exception("Cantidad inválida para producto: $id_producto");
        }
        
        if (isset($todos_los_productos[$id_producto])) {
            $producto = $todos_los_productos[$id_producto];
            
            // Verificar stock
            if ($producto['stock'] < $cantidad) {
                throw new Exception("No hay suficiente stock para: " . escape_output($producto['nombre']));
            }
            
            // Validar precio
            if (!validate_positive_decimal($producto['precio'])) {
                throw new Exception("Precio inválido para producto: " . escape_output($producto['nombre']));
            }
            
            $subtotal = $producto['precio'] * $cantidad;
            $total_pedido += $subtotal;
            
            $productos_json[] = [
                'id' => $id_producto,
                'nombre' => $producto['nombre'],
                'cantidad' => (int)$cantidad,
                'precio' => (float)$producto['precio'],
                'subtotal' => (float)$subtotal
            ];
        } else {
            throw new Exception("Producto no encontrado: $id_producto");
        }
    }
    
    // Validar total del pedido
    if ($total_pedido <= 0 || $total_pedido > 100000000) {
        throw new Exception("Total del pedido fuera de rango válido.");
    }
    
    // Verificar columnas de la tabla
    $stmt_columns = $pdo->query("SHOW COLUMNS FROM pedidos");
    $columns = $stmt_columns->fetchAll(PDO::FETCH_COLUMN);
    
    // Preparar datos para inserción
    $campos = ['cliente_id', 'nombre_cliente', 'email_cliente', 'telefono', 'productos', 'total', 'estado', 'direccion_envio'];
    $valores = [
        ':cliente_id' => rand(1000, 9999),
        ':nombre_cliente' => $nombre_cliente,
        ':email_cliente' => $email_cliente,
        ':telefono' => $telefono,
        ':productos' => json_encode($productos_json, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ':total' => $total_pedido,
        ':estado' => 'pendiente',
        ':direccion_envio' => $direccion_envio
    ];
    
    // Agregar notas si existe la columna
    if (in_array('notas', $columns) && !empty($notas)) {
        $campos[] = 'notas';
        $valores[':notas'] = $notas;
    }
    
    // Construir consulta SQL segura
    $campos_sql = implode(', ', $campos);
    $placeholders = ':' . implode(', :', $campos);
    
    $sql_pedido = "INSERT INTO pedidos ($campos_sql) VALUES ($placeholders)";
    
    // Ejecutar con prepared statement
    $stmt = $pdo->prepare($sql_pedido);
    $stmt->execute($valores);
    
    $pedido_id = $pdo->lastInsertId();
    
    // Insertar items y reducir stock
    $tabla_items_existe = $pdo->query("SHOW TABLES LIKE 'pedido_items'")->rowCount() > 0;
    $tabla_productos_existe = $pdo->query("SHOW TABLES LIKE 'productos'")->rowCount() > 0;
    
    if ($tabla_items_existe) {
        $sql_item = "INSERT INTO pedido_items (
            pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_item = $pdo->prepare($sql_item);
        
        foreach ($productos_json as $item) {
            $stmt_item->execute([
                $pedido_id,
                $item['id'],
                $item['nombre'],
                $item['cantidad'],
                $item['precio'],
                $item['subtotal']
            ]);
            
            // Reducir stock
            if ($tabla_productos_existe) {
                $sql_stock = "UPDATE productos SET stock = stock - ? WHERE id = ? AND stock >= ?";
                $stmt_stock = $pdo->prepare($sql_stock);
                $stmt_stock->execute([$item['cantidad'], $item['id'], $item['cantidad']]);
                
                if ($stmt_stock->rowCount() == 0) {
                    throw new Exception("Error al actualizar stock del producto: " . escape_output($item['nombre']));
                }
            }
        }
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    // Log de éxito
    log_security_event('PEDIDO', "Pedido #$pedido_id creado exitosamente por $email_cliente", 'INFO');
    
    // Enviar email (sin detener si falla)
    try {
        enviar_email_confirmacion($email_cliente, $nombre_cliente, $pedido_id, $productos_json, $total_pedido, $telefono, $direccion_envio, $notas);
    } catch (Exception $e) {
        log_security_event('EMAIL', 'Error al enviar confirmación: ' . $e->getMessage(), 'ERROR');
    }
    
    // Guardar datos en sesión
    $_SESSION['ultimo_pedido'] = [
        'id' => $pedido_id,
        'nombre' => $nombre_cliente,
        'email' => $email_cliente,
        'telefono' => $telefono,
        'total' => $total_pedido,
        'productos' => $productos_json
    ];
    
    // Limpiar carrito
    unset($_SESSION['carrito']);
    
    // Redirigir
    $_SESSION['pedido_exitoso'] = $pedido_id;
    header('Location: ' . BASE_URL . 'pedido_confirmado.php?id=' . $pedido_id);
    exit();
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    log_security_event('PEDIDO', 'Error PDO: ' . $e->getMessage(), 'ERROR');
    $_SESSION['error_pedido'] = "Error al procesar el pedido. Por favor, intenta nuevamente.";
    header('Location: ' . BASE_URL . 'checkout.php');
    exit();
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    log_security_event('PEDIDO', 'Error: ' . $e->getMessage(), 'ERROR');
    $_SESSION['error_pedido'] = $e->getMessage();
    header('Location: ' . BASE_URL . 'checkout.php');
    exit();
}

// Función auxiliar para enviar email
function enviar_email_confirmacion($email, $nombre, $pedido_id, $productos, $total, $telefono, $direccion, $notas) {
    $mail = new PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'tu_correo@gmail.com';
    $mail->Password = 'xxxx xxxx xxxx xxxx';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    $mail->setFrom('tu_correo@gmail.com', 'THE PRINT');
    $mail->addAddress($email, $nombre);
    
    $mail->isHTML(true);
    $mail->Subject = '✅ Confirmación de Pedido #' . str_pad($pedido_id, 5, '0', STR_PAD_LEFT) . ' - THE PRINT';
    
    // Construir HTML del email (usar escape_output para seguridad)
    $html_productos = '';
    foreach ($productos as $item) {
        $html_productos .= '<tr>
            <td style="padding: 10px;">' . escape_output($item['nombre']) . '</td>
            <td style="padding: 10px; text-align: center;">' . $item['cantidad'] . '</td>
            <td style="padding: 10px; text-align: right;">$ ' . number_format($item['precio'], 0, ',', '.') . '</td>
            <td style="padding: 10px; text-align: right;">$ ' . number_format($item['subtotal'], 0, ',', '.') . '</td>
        </tr>';
    }
    
    $mail->Body = '<!DOCTYPE html>
    <html><body style="font-family: Arial, sans-serif;">
        <h2>¡Gracias por tu compra, ' . escape_output($nombre) . '!</h2>
        <p>Pedido #' . str_pad($pedido_id, 5, '0', STR_PAD_LEFT) . '</p>
        <table>' . $html_productos . '</table>
        <p><strong>Total: $ ' . number_format($total, 0, ',', '.') . '</strong></p>
    </body></html>';
    
    $mail->send();
}
?>
