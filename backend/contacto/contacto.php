<?php
// 1. Incluimos la configuración (BD, BASE_URL)
// Usamos ../PHP/ para subir de 'contacto' a 'backend' y entrar a 'PHP'
require_once '../PHP/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - THE PRINT</title>
    
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/nav.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/footer.css" rel="stylesheet">
    <!-- CSS Específico para esta página -->
    <link href="<?php echo BASE_URL; ?>assets/css/style-contacto.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <!-- ======== 1. NAVEGACIÓN ======== -->
    <?php require_once '../PHP/nav.php'; ?>

    <!-- ======== 2. CONTENIDO DE CONTACTO ======== -->
    <div class="container my-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1 class="display-4">Ponte en Contacto</h1>
                <p class="lead text-muted">Estamos listos para ayudarte con tus proyectos de impresión.</p>
            </div>
        </div>

        <div class="row g-5">
            
            <!-- 
                Columna de Información de Contacto 
                Cambiamos 'col-lg-5' por 'col-lg-8 mx-auto' para centrar el contenido
            -->
            <div class="col-lg-8 mx-auto">
                <div class="contact-details-box p-4 rounded-3 shadow-sm">
                    <h3 class="mb-4"><i class="bi bi-person-rolodex me-2"></i>Nuestra Información</h3>
                    
                    <div class="contact-item d-flex mb-3">
                        <i class="bi bi-whatsapp fs-4 me-3 text-success"></i>
                        <div>
                            <strong>WhatsApp</strong><br>
                            <!-- El enlace wa.me usa el número sin el '+' -->
                            <a href="https://wa.me/573203150231" target="_blank">+57 320 315 0231</a>
                        </div>
                    </div>

                    <div class="contact-item d-flex mb-3">
                        <i class="bi bi-envelope-fill fs-4 me-3 text-primary"></i>
                        <div>
                            <strong>Correo Electrónico</strong><br>
                            <a href="mailto:ramirezfierros24@gmail.com">ramirezfierros24@gmail.com</a>
                        </div>
                    </div>

                    <div class="contact-item d-flex mb-3">
                        <i class="bi bi-geo-alt-fill fs-4 me-3 text-danger"></i>
                        <div>
                            <strong>Dirección</strong><br>
                            <!-- Esta es una dirección de ejemplo en Campoalegre -->
                            Carrera 10 #18-25<br>
                            Parque Principal, Campoalegre, Huila
                        </div>
                    </div>
                </div>
                
                <!-- Mapa de Google -->
                <div class="map-container mt-4 shadow-sm">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.0825345717!2d-75.32189318855523!3d2.684117097368681!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8e3b09062a4d7a0b%3A0x7d066373b9e59d9!2sParque%20Principal%20de%20Campoalegre!5e0!3m2!1ses!2sco!4v1729700057861!5m2!1ses!2sco" 
                        width="100%" 
                        height="350" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>

            <!-- Columna del Formulario de Contacto (ELIMINADA) -->

        </div>
    </div>

    <!-- ======== 3. PIE DE PÁGINA ======== -->
    <?php require_once '../PHP/footer.php'; ?>

    <!-- JS de Bootstrap -->
    <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script de validación de Bootstrap (ELIMINADO) -->
     <?php require_once '../PHP/chatbot.php'; ?>

</body>
</html>

