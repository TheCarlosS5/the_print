<?php
/**
 * Archivo: config.php
 * Descripción: Configuración general con medidas de seguridad
 */

// Incluir funciones de seguridad
require_once __DIR__ . '/security.php';

// ========== CONFIGURACIÓN DE BASE DE DATOS ==========
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'the_print');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ========== CONFIGURACIÓN DE RUTAS ==========
define('BASE_URL', 'http://localhost/PROYECTO/');

// Zona horaria
date_default_timezone_set('America/Bogota');

// ========== CONFIGURACIÓN DE ERRORES ==========
// En desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// En producción, descomentar esto:
/*
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');
*/

// ========== FUNCIÓN DE CONEXIÓN PDO SEGURA ==========
function obtener_conexion() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false, // Prevenir inyección SQL
            PDO::ATTR_PERSISTENT => false, // No usar conexiones persistentes por seguridad
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Log de conexión exitosa
        log_security_event('DATABASE', 'Conexión establecida exitosamente', 'INFO');
        
        return $pdo;
        
    } catch (PDOException $e) {
        // NO mostrar detalles del error en producción
        log_security_event('DATABASE', 'Error de conexión: ' . $e->getMessage(), 'ERROR');
        
        // En desarrollo, mostrar error detallado
        if (ini_get('display_errors')) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        } else {
            // En producción, mensaje genérico
            die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
        }
    }
}

// ========== FUNCIONES DE SEGURIDAD ADICIONALES ==========

/**
 * Verificar si el usuario es administrador autenticado
 */
function is_admin() {
    return isset($_SESSION['admin_autenticado']) && $_SESSION['admin_autenticado'] === true;
}

/**
 * Requerir autenticación de administrador
 */
function require_admin() {
    if (!is_admin()) {
        log_security_event('ADMIN_ACCESS', 'Intento de acceso no autorizado', 'WARNING');
        header('Location: ' . BASE_URL . 'panel/admin/login.php');
        exit();
    }
}

/**
 * Verificar timeout de sesión
 */
function check_session_timeout($timeout = 3600) {
    if (isset($_SESSION['admin_login_time'])) {
        if (time() - $_SESSION['admin_login_time'] > $timeout) {
            log_security_event('SESSION', 'Sesión expirada por timeout', 'INFO');
            session_destroy();
            return false;
        }
    }
    return true;
}
?>
