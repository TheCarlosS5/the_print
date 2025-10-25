<?php
/**
 * Archivo: security.php
 * Descripción: Funciones de seguridad centralizadas
 */

// ========== CONFIGURACIÓN DE SEGURIDAD ==========

// Forzar HTTPS (simulado en desarrollo)
function force_https() {
    // En producción, descomentar esto:
    /*
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
    */
    
    // En desarrollo, solo registrar que se usaría HTTPS
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        error_log('[SECURITY] En producción se forzaría HTTPS para: ' . $_SERVER['REQUEST_URI']);
    }
}

// Configurar headers de seguridad
function set_security_headers() {
    // Prevenir clickjacking
    header('X-Frame-Options: DENY');
    
    // Prevenir XSS
    header('X-XSS-Protection: 1; mode=block');
    
    // Prevenir MIME sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Política de referencia
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (CSP)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; font-src 'self' https://cdn.jsdelivr.net;");
    
    // HSTS (HTTP Strict Transport Security) - Solo en producción
    // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Configurar sesiones seguras
function configure_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración de cookies de sesión seguras
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
        ini_set('session.cookie_samesite', 'Strict');
        
        // Regenerar ID de sesión periódicamente
        session_start();
        
        // Regenerar ID cada 30 minutos
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

// ========== VALIDACIÓN Y SANITIZACIÓN ==========

/**
 * Limpiar y validar entrada de texto
 */
function clean_input($data) {
    if (is_array($data)) {
        return array_map('clean_input', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validar email
 */
function validate_email($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar teléfono (formato colombiano)
 */
function validate_phone($phone) {
    // Eliminar caracteres no numéricos
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Validar que tenga 10 dígitos (formato colombiano)
    return strlen($phone) === 10 && preg_match('/^3[0-9]{9}$/', $phone);
}

/**
 * Validar número entero positivo
 */
function validate_positive_int($value) {
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
}

/**
 * Validar decimal positivo
 */
function validate_positive_decimal($value) {
    return filter_var($value, FILTER_VALIDATE_FLOAT) !== false && $value > 0;
}

/**
 * Validar longitud de texto
 */
function validate_length($text, $min, $max) {
    $length = mb_strlen($text, 'UTF-8');
    return $length >= $min && $length <= $max;
}

/**
 * Validar código OTP
 */
function validate_otp($code) {
    return preg_match('/^[0-9]{6}$/', $code);
}

// ========== PROTECCIÓN CONTRA ATAQUES ==========

/**
 * Protección contra CSRF
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Rate limiting simple (protección contra fuerza bruta)
 */
function check_rate_limit($action, $max_attempts = 5, $time_window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'rate_limit_' . $action . '_' . $ip;
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'start_time' => time()];
    }
    
    $data = $_SESSION[$key];
    
    // Resetear si pasó el tiempo
    if (time() - $data['start_time'] > $time_window) {
        $_SESSION[$key] = ['count' => 1, 'start_time' => time()];
        return true;
    }
    
    // Verificar límite
    if ($data['count'] >= $max_attempts) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}

/**
 * Prevenir inyección SQL (usar siempre con PDO preparado)
 */
function sanitize_sql_input($input) {
    // Esta función es un respaldo, SIEMPRE usar prepared statements
    return preg_replace('/[^a-zA-Z0-9_]/', '', $input);
}

/**
 * Prevenir XSS en salida
 */
function escape_output($data) {
    if (is_array($data)) {
        return array_map('escape_output', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// ========== LOGGING DE SEGURIDAD ==========

/**
 * Registrar evento de seguridad
 */
function log_security_event($type, $message, $severity = 'INFO') {
    $log_file = __DIR__ . '/../../logs/security.log';
    $log_dir = dirname($log_file);
    
    // Crear directorio de logs si no existe
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0750, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    
    $log_entry = sprintf(
        "[%s] [%s] [%s] IP: %s | %s | User-Agent: %s\n",
        $timestamp,
        $severity,
        $type,
        $ip,
        $message,
        substr($user_agent, 0, 100)
    );
    
    error_log($log_entry, 3, $log_file);
}

// ========== VALIDACIÓN DE ARCHIVOS (si subes archivos) ==========

/**
 * Validar archivo subido
 */
function validate_uploaded_file($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = 2097152) {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Error en el archivo'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error al subir archivo'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Archivo demasiado grande'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    return ['success' => true];
}

// ========== INICIALIZACIÓN AUTOMÁTICA ==========

// Aplicar configuración de seguridad al incluir este archivo
force_https();
set_security_headers();
configure_secure_session();
?>
