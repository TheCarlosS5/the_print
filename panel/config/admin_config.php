<?php
// Configuración del Administrador
define('ADMIN_EMAIL', 'carlosstivengutierrezramirez@gmail.com'); // ← CAMBIAR por tu correo

// Configuración SMTP para envío de emails
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'carlosstivengutierrezramirez@gmail.com'); // ← CAMBIAR
define('SMTP_PASSWORD', 'rfimtsmyllwlfmnl'); // ← Contraseña de aplicación de Google
define('SMTP_FROM_EMAIL', 'carlosstivengutierrezramirez@gmail.com'); // ← CAMBIAR
define('SMTP_FROM_NAME', 'THE PRINT - Admin Panel');

// Tiempo de expiración del código OTP (en segundos)
define('OTP_EXPIRATION', 600); // 10 minutos

// Tiempo de expiración de sesión de administrador (en segundos)
define('SESSION_TIMEOUT', 3600); // 1 hora (60 minutos)
?>
