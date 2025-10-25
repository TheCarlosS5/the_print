<?php
session_start();

// Incluir archivos de configuración
require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/mailer.php';

// Si ya está autenticado, redirigir al dashboard
if(isset($_SESSION['admin_autenticado']) && $_SESSION['admin_autenticado'] === true) {
    header('Location: dashboard.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Solicitar código OTP
if(isset($_POST['solicitar_codigo'])) {
    try {
        // Generar código de 6 dígitos
        $codigo = sprintf("%06d", mt_rand(1, 999999));
        $fecha_expiracion = date('Y-m-d H:i:s', time() + OTP_EXPIRATION);
        
        // Guardar en base de datos
        $stmt = $pdo->prepare("INSERT INTO admin_otp (codigo, fecha_expiracion, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$codigo, $fecha_expiracion, $_SERVER['REMOTE_ADDR']]);
        
        // Enviar por email
        if(enviarCodigoOTP(ADMIN_EMAIL, $codigo)) {
            $_SESSION['otp_solicitado'] = true;
            $_SESSION['otp_id'] = $pdo->lastInsertId();
            $mensaje = 'Código enviado a ' . ADMIN_EMAIL;
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al enviar el código. Verifica la configuración de PHPMailer.';
            $tipo_mensaje = 'error';
        }
    } catch(PDOException $e) {
        $mensaje = 'Error en el sistema: ' . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Verificar código OTP
if(isset($_POST['verificar_codigo'])) {
    $codigo_ingresado = trim($_POST['codigo']);
    
    if(!empty($codigo_ingresado) && isset($_SESSION['otp_id'])) {
        try {
            // Buscar código válido no usado y no expirado
            $stmt = $pdo->prepare("
                SELECT id FROM admin_otp 
                WHERE id = ? AND codigo = ? AND usado = 0 
                AND fecha_expiracion > NOW() AND ip_address = ?
            ");
            $stmt->execute([$_SESSION['otp_id'], $codigo_ingresado, $_SERVER['REMOTE_ADDR']]);
            
            if($stmt->rowCount() > 0) {
                // Marcar código como usado
                $stmt = $pdo->prepare("UPDATE admin_otp SET usado = 1 WHERE id = ?");
                $stmt->execute([$_SESSION['otp_id']]);
                
                // Crear sesión de administrador
                $_SESSION['admin_autenticado'] = true;
                $_SESSION['admin_email'] = ADMIN_EMAIL;
                $_SESSION['admin_login_time'] = time();
                
                // Registrar sesión
                $stmt = $pdo->prepare("INSERT INTO admin_sesiones (session_id, ip_address, user_agent) VALUES (?, ?, ?)");
                $stmt->execute([session_id(), $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
                
                unset($_SESSION['otp_solicitado']);
                unset($_SESSION['otp_id']);
                
                header('Location: dashboard.php');
                exit;
            } else {
                $mensaje = 'Código inválido o expirado';
                $tipo_mensaje = 'error';
            }
        } catch(PDOException $e) {
            $mensaje = 'Error al verificar código: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = 'Por favor ingresa el código';
        $tipo_mensaje = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador - THE PRINT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .login-header {
            background: white;
            padding: 2rem;
            text-align: center;
        }
        
        .admin-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .login-body {
            background: white;
            padding: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .code-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .back-link {
            color: #667eea;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
        }
        
        .back-link:hover {
            color: #764ba2;
            transform: translateX(-5px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12 login-container">
                <div class="card login-card">
                    <div class="login-header">
                        <i class="bi bi-shield-lock-fill admin-icon"></i>
                        <h2 class="mb-0">Acceso Administrador</h2>
                        <p class="text-muted mb-0">THE PRINT</p>
                    </div>
                    
                    <div class="login-body">
                        <?php if(!empty($mensaje)): ?>
                            <div class="alert alert-<?php echo $tipo_mensaje == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                                <i class="bi bi-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
                                <?php echo htmlspecialchars($mensaje); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(!isset($_SESSION['otp_solicitado'])): ?>
                            <!-- Formulario para solicitar código -->
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <p class="text-muted">
                                        Haz clic en el botón para recibir un código de acceso temporal en tu correo electrónico.
                                    </p>
                                    <div class="alert alert-info">
                                        <i class="bi bi-envelope-fill me-2"></i>
                                        Se enviará a: <strong><?php echo ADMIN_EMAIL; ?></strong>
                                    </div>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" name="solicitar_codigo" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send-fill me-2"></i>
                                        Solicitar Código de Acceso
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- Formulario para verificar código -->
                            <form method="POST">
                                <div class="mb-4 text-center">
                                    <div class="alert alert-success">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        Código enviado exitosamente
                                    </div>
                                    <p class="text-muted">
                                        Revisa tu correo e ingresa el código de 6 dígitos.
                                    </p>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="codigo" class="form-label text-center w-100">
                                        <i class="bi bi-key-fill me-1"></i>
                                        Código de Verificación
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg code-input" 
                                           id="codigo" 
                                           name="codigo" 
                                           maxlength="6" 
                                           pattern="[0-9]{6}" 
                                           placeholder="000000" 
                                           required 
                                           autofocus>
                                    <small class="text-muted d-block text-center mt-2">
                                        El código expira en <?php echo OTP_EXPIRATION / 60; ?> minutos
                                    </small>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" name="verificar_codigo" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-circle me-2"></i>
                                        Verificar Código
                                    </button>
                                </div>
                                
                                <div class="text-center">
                                    <form method="POST" style="display: inline;">
                                        <button type="submit" name="solicitar_codigo" class="btn btn-link text-decoration-none">
                                            <i class="bi bi-arrow-clockwise me-1"></i>
                                            Reenviar código
                                        </button>
                                    </form>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <a href="../../index.php" class="back-link">
                                <i class="bi bi-arrow-left"></i>
                                Volver al sitio web
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-formato del código (solo números)
        document.querySelector('#codigo')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>
