<?php
require_once __DIR__ . '/../config/admin_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

function enviarCodigoOTP($email, $codigo) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Remitente y destinatario
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        
        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Código de Acceso - Panel Administrador';
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
        </head>
        <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #667eea; margin: 0;'>THE PRINT</h1>
                    <p style='color: #666; margin-top: 10px;'>Panel de Administración</p>
                </div>
                
                <h2 style='color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px;'>Código de Acceso</h2>
                
                <p style='color: #555; font-size: 16px; line-height: 1.6;'>
                    Has solicitado acceso al panel de administrador.
                </p>
                
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px; margin: 30px 0;'>
                    <p style='color: white; margin: 0 0 10px 0; font-size: 14px;'>Tu código de verificación es:</p>
                    <h1 style='color: white; margin: 0; font-size: 48px; letter-spacing: 10px; font-weight: bold;'>
                        {$codigo}
                    </h1>
                </div>
                
                <div style='background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        <strong>⚠️ Importante:</strong> Este código expira en <strong>" . (OTP_EXPIRATION / 60) . " minutos</strong>.
                    </p>
                </div>
                
                <p style='color: #555; font-size: 14px; line-height: 1.6;'>
                    Si no solicitaste este código, puedes ignorar este mensaje de forma segura.
                </p>
                
                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>
                        © 2025 THE PRINT - Panel Administrativo<br>
                        Este es un correo automático, por favor no responder.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->AltBody = "Tu código de acceso es: {$codigo}\nEste código expira en " . (OTP_EXPIRATION / 60) . " minutos.";
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar email OTP: " . $mail->ErrorInfo);
        return false;
    }
}
?>
