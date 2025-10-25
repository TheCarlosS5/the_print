<?php
// No necesita estar en sesión, solo es HTML/CSS/JS
?>

<!-- ChatBot Widget -->
<div id="chatbot-container">
    <!-- Botón flotante con ondas -->
    <div class="chatbot-button-wrapper">
        <div class="pulse-ring"></div>  
        <div class="pulse-ring pulse-ring-delayed"></div>
        <button id="chatbot-toggle" class="chatbot-button" aria-label="Abrir chat">
            <i class="bi bi-chat-dots-fill chat-icon"></i>
            <i class="bi bi-x-lg close-icon"></i>
            <span class="chatbot-badge" id="unread-badge">1</span>
        </button>
    </div>

    <!-- Ventana del chat -->
    <div id="chatbot-window" class="chatbot-window">
        <!-- Header -->
        <div class="chatbot-header">
            <div class="d-flex align-items-center">
                <div class="chatbot-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="ms-3">
                    <h6 class="mb-0">Asistente Virtual</h6>
                    <small class="text-white-50">
                        <span class="status-dot"></span> En línea
                    </small>
                </div>
            </div>
            <button class="chatbot-close" id="chatbot-close" aria-label="Cerrar chat">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Mensajes -->
        <div class="chatbot-messages" id="chatbot-messages">
            <div class="message bot-message">
                <div class="message-avatar">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="message-content">
                    <p>¡Hola! 👋 Soy el asistente virtual de <strong>THE PRINT</strong></p>
                    <p>¿En qué puedo ayudarte hoy?</p>
                </div>
            </div>

            <!-- Opciones rápidas iniciales -->
            <div class="quick-options" id="quick-options">
                <button class="quick-option" onclick="enviarOpcion('productos')">
                    <i class="bi bi-box-seam me-2"></i>Ver Productos
                </button>
                <button class="quick-option" onclick="enviarOpcion('horarios')">
                    <i class="bi bi-clock me-2"></i>Horarios de Atención
                </button>
                <button class="quick-option" onclick="enviarOpcion('envios')">
                    <i class="bi bi-truck me-2"></i>Información de Envíos
                </button>
                <button class="quick-option" onclick="enviarOpcion('contacto')">
                    <i class="bi bi-telephone me-2"></i>Contacto
                </button>
            </div>
        </div>

        <!-- Input -->
        <div class="chatbot-input">
            <input type="text" 
                   id="chatbot-input-field" 
                   placeholder="Escribe tu mensaje..." 
                   autocomplete="off"
                   maxlength="500">
            <button id="chatbot-send" class="chatbot-send-btn">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>
</div>

<!-- Estilos del ChatBot -->
<style>
    #chatbot-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    /* Wrapper del botón con ondas */
    .chatbot-button-wrapper {
        position: relative;
        width: 60px;
        height: 60px;
    }

    /* Ondas de pulso */
    .pulse-ring {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 60px;
        height: 60px;
        border: 3px solid #667eea;
        border-radius: 50%;
        animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
        opacity: 0;
    }

    .pulse-ring-delayed {
        animation-delay: 1s;
    }

    @keyframes pulse-ring {
        0% {
            width: 60px;
            height: 60px;
            opacity: 1;
        }
        100% {
            width: 120px;
            height: 120px;
            opacity: 0;
        }
    }

    /* Botón flotante */
    .chatbot-button {
        position: absolute;
        top: 0;
        left: 0;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.5);
        color: white;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        position: relative;
        overflow: hidden;
    }

    .chatbot-button::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .chatbot-button:hover::before {
        width: 100px;
        height: 100px;
    }

    .chatbot-button:hover {
        transform: scale(1.15) rotate(10deg);
        box-shadow: 0 8px 30px rgba(102, 126, 234, 0.7);
    }

    .chatbot-button:active {
        transform: scale(0.95);
    }

    /* Iconos del botón */
    .chat-icon, .close-icon {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        transition: all 0.3s ease;
    }

    .close-icon {
        opacity: 0;
        transform: translate(-50%, -50%) rotate(-90deg) scale(0);
    }

    .chatbot-button.active .chat-icon {
        opacity: 0;
        transform: translate(-50%, -50%) rotate(90deg) scale(0);
    }

    .chatbot-button.active .close-icon {
        opacity: 1;
        transform: translate(-50%, -50%) rotate(0deg) scale(1);
    }

    /* Badge con animación rebote */
    .chatbot-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ff4444;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        animation: badge-bounce 1s ease infinite;
        box-shadow: 0 2px 8px rgba(255, 68, 68, 0.5);
    }

    @keyframes badge-bounce {
        0%, 100% { 
            transform: translateY(0) scale(1); 
        }
        50% { 
            transform: translateY(-8px) scale(1.1); 
        }
    }

    .chatbot-badge.hidden {
        animation: badge-hide 0.3s ease forwards;
    }

    @keyframes badge-hide {
        to {
            transform: scale(0);
            opacity: 0;
        }
    }

    /* Ventana del chat con animación */
    .chatbot-window {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 380px;
        max-width: calc(100vw - 40px);
        height: 600px;
        max-height: calc(100vh - 120px);
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        display: none;
        flex-direction: column;
        overflow: hidden;
        transform-origin: bottom right;
        animation: window-pop-in 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    .chatbot-window.active {
        display: flex;
    }

    @keyframes window-pop-in {
        0% {
            opacity: 0;
            transform: scale(0.3) translateY(50px);
        }
        70% {
            transform: scale(1.05) translateY(-5px);
        }
        100% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .chatbot-window.closing {
        animation: window-pop-out 0.3s ease forwards;
    }

    @keyframes window-pop-out {
        to {
            opacity: 0;
            transform: scale(0.8) translateY(20px);
        }
    }

    /* Header con gradiente animado */
    .chatbot-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        background-size: 200% 200%;
        animation: gradient-shift 3s ease infinite;
        color: white;
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @keyframes gradient-shift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .chatbot-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        animation: avatar-bounce 2s ease infinite;
    }

    @keyframes avatar-bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }

    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #4ade80;
        margin-right: 5px;
        animation: status-blink 2s ease infinite;
        box-shadow: 0 0 10px #4ade80;
    }

    @keyframes status-blink {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.9); }
    }

    .chatbot-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .chatbot-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg) scale(1.1);
    }

    /* Mensajes con animación de entrada */
    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
    }

    .chatbot-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chatbot-messages::-webkit-scrollbar-thumb {
        background: #cbd5e0;
        border-radius: 3px;
    }

    .message {
        display: flex;
        margin-bottom: 16px;
        animation: message-slide-in 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    @keyframes message-slide-in {
        from { 
            opacity: 0; 
            transform: translateY(20px) scale(0.8);
        }
        to { 
            opacity: 1; 
            transform: translateY(0) scale(1);
        }
    }

    .bot-message {
        justify-content: flex-start;
    }

    .user-message {
        justify-content: flex-end;
    }

    .message-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        animation: avatar-pop 0.3s ease;
    }

    @keyframes avatar-pop {
        0% { transform: scale(0); }
        70% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .user-message .message-avatar {
        background: #e2e8f0;
        color: #475569;
        order: 2;
        margin-left: 8px;
    }

    .message-content {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 12px;
        margin-left: 8px;
        position: relative;
    }

    .bot-message .message-content {
        background: white;
        color: #1e293b;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .user-message .message-content {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        margin-left: 0;
        margin-right: 8px;
    }

    .message-content p {
        margin: 0 0 8px 0;
    }

    .message-content p:last-child {
        margin-bottom: 0;
    }

    .message-content a {
        color: #667eea;
        text-decoration: underline;
        transition: all 0.2s;
    }

    .message-content a:hover {
        color: #764ba2;
    }

    .user-message .message-content a {
        color: white;
    }

    /* Opciones rápidas con animación stagger */
    .quick-options {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }

    .quick-option {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        text-align: left;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        font-size: 14px;
        color: #475569;
        position: relative;
        overflow: hidden;
        animation: option-slide-in 0.4s ease backwards;
    }

    .quick-option:nth-child(1) { animation-delay: 0.1s; }
    .quick-option:nth-child(2) { animation-delay: 0.2s; }
    .quick-option:nth-child(3) { animation-delay: 0.3s; }
    .quick-option:nth-child(4) { animation-delay: 0.4s; }

    @keyframes option-slide-in {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .quick-option::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.1), transparent);
        transition: left 0.5s;
    }

    .quick-option:hover::before {
        left: 100%;
    }

    .quick-option:hover {
        background: #f8f9fa;
        border-color: #667eea;
        color: #667eea;
        transform: translateX(8px) scale(1.02);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }

    .quick-option:active {
        transform: translateX(8px) scale(0.98);
    }

    /* Input con efecto focus */
    .chatbot-input {
        padding: 16px;
        background: white;
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 8px;
    }

    #chatbot-input-field {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 10px 16px;
        font-size: 14px;
        outline: none;
        transition: all 0.3s ease;
    }

    #chatbot-input-field:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        transform: scale(1.02);
    }

    .chatbot-send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        position: relative;
        overflow: hidden;
    }

    .chatbot-send-btn::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .chatbot-send-btn:hover::before {
        width: 80px;
        height: 80px;
    }

    .chatbot-send-btn:hover {
        transform: scale(1.15) rotate(15deg);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .chatbot-send-btn:active {
        transform: scale(0.9) rotate(0deg);
    }

    .chatbot-send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Indicador de escritura mejorado */
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 12px 16px;
        background: white;
        border-radius: 12px;
        width: fit-content;
        margin-left: 40px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        animation: message-slide-in 0.3s ease;
    }

    .typing-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        animation: typing-bounce 1.4s infinite ease-in-out;
    }

    .typing-dot:nth-child(1) {
        animation-delay: 0s;
    }

    .typing-dot:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dot:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing-bounce {
        0%, 60%, 100% { 
            transform: translateY(0) scale(1);
            opacity: 0.7;
        }
        30% { 
            transform: translateY(-12px) scale(1.2);
            opacity: 1;
        }
    }

    /* Responsive */
    @media (max-width: 480px) {
        .chatbot-window {
            width: calc(100vw - 20px);
            right: 10px;
            bottom: 90px;
        }

        #chatbot-container {
            right: 10px;
            bottom: 10px;
        }
    }
</style>

<!-- JavaScript del ChatBot (continuación en el siguiente mensaje...) -->
<!-- JavaScript del ChatBot mejorado -->
<script>
    // ========== ELEMENTOS DEL DOM ==========
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatbotInput = document.getElementById('chatbot-input-field');
    const chatbotSend = document.getElementById('chatbot-send');
    const unreadBadge = document.getElementById('unread-badge');

    let chatAbierto = false;

    // ========== EFECTOS DE PARTÍCULAS ==========
    function crearParticulas(elemento) {
        const rect = elemento.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        for (let i = 0; i < 8; i++) {
            const particula = document.createElement('div');
            particula.style.position = 'fixed';
            particula.style.width = '6px';
            particula.style.height = '6px';
            particula.style.borderRadius = '50%';
            particula.style.background = `hsl(${Math.random() * 60 + 240}, 70%, 60%)`;
            particula.style.pointerEvents = 'none';
            particula.style.zIndex = '10000';
            particula.style.left = centerX + 'px';
            particula.style.top = centerY + 'px';
            
            document.body.appendChild(particula);

            const angulo = (Math.PI * 2 * i) / 8;
            const distancia = 50 + Math.random() * 30;
            const x = Math.cos(angulo) * distancia;
            const y = Math.sin(angulo) * distancia;

            particula.animate([
                { 
                    transform: 'translate(0, 0) scale(1)',
                    opacity: 1
                },
                { 
                    transform: `translate(${x}px, ${y}px) scale(0)`,
                    opacity: 0
                }
            ], {
                duration: 600 + Math.random() * 200,
                easing: 'cubic-bezier(0, 0.5, 0.5, 1)'
            }).onfinish = () => particula.remove();
        }
    }

    // ========== VIBRACIÓN (si está disponible) ==========
    function vibrar(patron = [10]) {
        if ('vibrate' in navigator) {
            navigator.vibrate(patron);
        }
    }

    // ========== TOGGLE CHAT CON ANIMACIONES ==========
    chatbotToggle.addEventListener('click', () => {
        if (!chatAbierto) {
            abrirChat();
        } else {
            cerrarChat();
        }
    });

    function abrirChat() {
        chatAbierto = true;
        chatbotWindow.classList.add('active');
        chatbotToggle.classList.add('active');
        unreadBadge.classList.add('hidden');
        crearParticulas(chatbotToggle);
        vibrar([10, 5, 10]);
        
        // Enfocar input después de la animación
        setTimeout(() => {
            chatbotInput.focus();
        }, 300);
    }

    function cerrarChat() {
        chatAbierto = false;
        chatbotWindow.classList.add('closing');
        chatbotToggle.classList.remove('active');
        vibrar([5]);
        
        setTimeout(() => {
            chatbotWindow.classList.remove('active', 'closing');
        }, 300);
    }

    chatbotClose.addEventListener('click', cerrarChat);

    // ========== ENVIAR MENSAJE ==========
    chatbotSend.addEventListener('click', () => {
        enviarMensaje();
        crearParticulas(chatbotSend);
        vibrar([5]);
    });

    chatbotInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            enviarMensaje();
            vibrar([5]);
        }
    });

    // Efecto de escritura en el input
    chatbotInput.addEventListener('input', () => {
        if (chatbotInput.value.length > 0) {
            chatbotSend.style.transform = 'scale(1.1)';
        } else {
            chatbotSend.style.transform = 'scale(1)';
        }
    });

    function enviarMensaje() {
        const mensaje = chatbotInput.value.trim();
        if (!mensaje) return;

        // Deshabilitar input temporalmente
        chatbotInput.disabled = true;
        chatbotSend.disabled = true;

        // Agregar mensaje del usuario con animación
        agregarMensaje(mensaje, 'user');
        chatbotInput.value = '';

        // Mostrar indicador de escritura
        mostrarEscribiendo();

        // Responder después de un delay realista
        const tiempoRespuesta = 800 + Math.random() * 1200;
        setTimeout(() => {
            const respuesta = obtenerRespuesta(mensaje);
            quitarEscribiendo();
            agregarMensaje(respuesta.texto, 'bot', respuesta.opciones);
            
            // Reactivar input
            chatbotInput.disabled = false;
            chatbotSend.disabled = false;
            chatbotInput.focus();
            
            // Vibración de respuesta
            vibrar([10]);
        }, tiempoRespuesta);
    }

    function agregarMensaje(texto, tipo, opciones = null) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${tipo}-message`;
        messageDiv.style.opacity = '0';

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.innerHTML = tipo === 'bot' 
            ? '<i class="bi bi-robot"></i>' 
            : '<i class="bi bi-person-fill"></i>';

        const content = document.createElement('div');
        content.className = 'message-content';
        content.innerHTML = `<p>${texto}</p>`;

        messageDiv.appendChild(avatar);
        messageDiv.appendChild(content);

        // Insertar antes de las opciones rápidas
        const quickOptions = document.getElementById('quick-options');
        if (quickOptions) {
            chatbotMessages.insertBefore(messageDiv, quickOptions);
        } else {
            chatbotMessages.appendChild(messageDiv);
        }

        // Animar entrada del mensaje
        requestAnimationFrame(() => {
            messageDiv.style.opacity = '1';
        });

        // Agregar opciones si existen
        if (opciones) {
            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'quick-options';
            optionsDiv.style.opacity = '0';
            
            opciones.forEach((opcion, index) => {
                const btn = document.createElement('button');
                btn.className = 'quick-option';
                btn.innerHTML = `<i class="bi ${opcion.icono} me-2"></i>${opcion.texto}`;
                btn.style.animationDelay = `${0.1 + index * 0.1}s`;
                btn.onclick = () => {
                    enviarOpcion(opcion.accion);
                    crearParticulas(btn);
                    vibrar([5]);
                };
                optionsDiv.appendChild(btn);
            });
            
            chatbotMessages.appendChild(optionsDiv);
            
            requestAnimationFrame(() => {
                optionsDiv.style.opacity = '1';
            });
        }

        // Scroll suave al final
        chatbotMessages.scrollTo({
            top: chatbotMessages.scrollHeight,
            behavior: 'smooth'
        });
    }

    function mostrarEscribiendo() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'typing-indicator';
        typingDiv.className = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        chatbotMessages.appendChild(typingDiv);
        
        chatbotMessages.scrollTo({
            top: chatbotMessages.scrollHeight,
            behavior: 'smooth'
        });
    }

    function quitarEscribiendo() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.style.animation = 'message-slide-out 0.3s ease forwards';
            setTimeout(() => typingIndicator.remove(), 300);
        }
    }

    function enviarOpcion(opcion) {
        // Eliminar opciones rápidas con animación
        const quickOptions = document.querySelectorAll('.quick-options');
        quickOptions.forEach(opt => {
            opt.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => opt.remove(), 300);
        });

        // Esperar a que se eliminen las opciones
        setTimeout(() => {
            agregarMensaje(obtenerTextoOpcion(opcion), 'user');

            mostrarEscribiendo();
            setTimeout(() => {
                const respuesta = obtenerRespuesta(opcion);
                quitarEscribiendo();
                agregarMensaje(respuesta.texto, 'bot', respuesta.opciones);
                vibrar([10]);
            }, 1000);
        }, 100);
    }

    function obtenerTextoOpcion(accion) {
        const textos = {
            'productos': 'Ver Productos',
            'horarios': 'Horarios de Atención',
            'envios': 'Información de Envíos',
            'contacto': 'Contacto',
            'ver_productos': 'Ir al Catálogo',
            'ver_contacto': 'Ir a Contacto'
        };
        return textos[accion] || accion;
    }

    // ========== BASE DE CONOCIMIENTO ==========
    function obtenerRespuesta(mensaje) {
        mensaje = mensaje.toLowerCase();

        // Productos
        if (mensaje.includes('producto') || mensaje === 'productos') {
            return {
                texto: '🎨 Contamos con una amplia gama de productos:<br><br>' +
                       '🖨️ <strong>Impresoras</strong> - Multifuncionales y láser<br>' +
                       '💧 <strong>Consumibles</strong> - Tintas y toners originales<br>' +
                       '📄 <strong>Papelería</strong> - Todo para tu oficina<br>' +
                       '🔧 <strong>Repuestos</strong> - Partes y accesorios<br><br>' +
                       '<a href="<?php echo BASE_URL; ?>backend/productos/productos.php">Ver catálogo completo →</a>',
                opciones: [
                    { texto: 'Ir al Catálogo', icono: 'bi-arrow-right-circle', accion: 'ver_productos' }
                ]
            };
        }

        // Horarios
        if (mensaje.includes('horario') || mensaje.includes('hora') || mensaje === 'horarios') {
            return {
                texto: '🕐 <strong>Horarios de Atención:</strong><br><br>' +
                       '📅 Lunes a Viernes: 8:00 AM - 6:00 PM<br>' +
                       '📅 Sábados: 9:00 AM - 1:00 PM<br>' +
                       '📅 Domingos: Cerrado<br><br>' +
                       '¡Estamos listos para atenderte! 💪',
                opciones: null
            };
        }

        // Envíos
        if (mensaje.includes('envio') || mensaje.includes('envío') || mensaje === 'envios') {
            return {
                texto: '🚚 <strong>Información de Envíos:</strong><br><br>' +
                       '✅ Envío GRATIS en compras superiores a $100.000<br>' +
                       '✅ Entregas en 24-48 horas en Bogotá<br>' +
                       '✅ Cobertura nacional<br>' +
                       '✅ Rastreo en tiempo real<br><br>' +
                       '¿Tienes alguna pregunta específica sobre envíos?',
                opciones: null
            };
        }

        // Contacto
        if (mensaje.includes('contacto') || mensaje.includes('contactar') || mensaje === 'contacto') {
            return {
                texto: '📞 <strong>¿Cómo contactarnos?</strong><br><br>' +
                       '📧 Email: theprint.compramelo@gmail.com<br>' +
                       '📱 WhatsApp: +57 318 429 8853<br>' +
                       '📍 Bogotá, Colombia<br><br>' +
                       '<a href="<?php echo BASE_URL; ?>backend/contacto/contacto.php">Formulario de contacto →</a>',
                opciones: [
                    { texto: 'Ir a Contacto', icono: 'bi-arrow-right-circle', accion: 'ver_contacto' }
                ]
            };
        }

        // Redirecciones
        if (mensaje === 'ver_productos') {
            window.location.href = '<?php echo BASE_URL; ?>backend/productos/productos.php';
            return { texto: '🔄 Redirigiendo al catálogo...', opciones: null };
        }

        if (mensaje === 'ver_contacto') {
            window.location.href = '<?php echo BASE_URL; ?>backend/contacto/contacto.php';
            return { texto: '🔄 Redirigiendo a contacto...', opciones: null };
        }

        // Precios
        if (mensaje.includes('precio') || mensaje.includes('cuesta') || mensaje.includes('valor') || mensaje.includes('cuanto')) {
            return {
                texto: '💰 <strong>Precios competitivos</strong><br><br>' +
                       'Los precios varían según el producto. Te invito a explorar nuestro catálogo donde encontrarás:<br><br>' +
                       '💵 Precios competitivos<br>' +
                       '🎁 Descuentos especiales<br>' +
                       '🔥 Ofertas semanales<br><br>' +
                       '<a href="<?php echo BASE_URL; ?>backend/productos/productos.php">Ver precios →</a>',
                opciones: null
            };
        }

        // Pago
        if (mensaje.includes('pago') || mensaje.includes('pagar') || mensaje.includes('tarjeta')) {
            return {
                texto: '💳 <strong>Métodos de Pago:</strong><br><br>' +
                       '✅ Transferencia bancaria<br>' +
                       '✅ Efectivo contra entrega<br>' +
                       '✅ Pago en línea (próximamente)<br><br>' +
                       'Todos nuestros pagos son 100% seguros. 🔒',
                opciones: null
            };
        }

        // Garantía
        if (mensaje.includes('garantia') || mensaje.includes('garantía') || mensaje.includes('devolucion') || mensaje.includes('devolución')) {
            return {
                texto: '🛡️ <strong>Garantía y Devoluciones:</strong><br><br>' +
                       '✅ Garantía de 30 días en todos los productos<br>' +
                       '✅ Cambios sin costo adicional<br>' +
                       '✅ Soporte técnico incluido<br><br>' +
                       'Tu satisfacción es nuestra prioridad. 😊',
                opciones: null
            };
        }

        // Saludos
        if (mensaje.includes('hola') || mensaje.includes('buenas') || mensaje.includes('buenos días') || mensaje.includes('buenas tardes') || mensaje.includes('buenos dias') || mensaje.includes('hi')) {
            const hora = new Date().getHours();
            let saludo = '¡Hola!';
            if (hora >= 6 && hora < 12) saludo = '¡Buenos días!';
            else if (hora >= 12 && hora < 19) saludo = '¡Buenas tardes!';
            else saludo = '¡Buenas noches!';

            return {
                texto: `${saludo} 👋 Bienvenido a THE PRINT. ¿En qué puedo ayudarte hoy?`,
                opciones: [
                    { texto: 'Ver Productos', icono: 'bi-box-seam', accion: 'productos' },
                    { texto: 'Horarios', icono: 'bi-clock', accion: 'horarios' },
                    { texto: 'Contacto', icono: 'bi-telephone', accion: 'contacto' }
                ]
            };
        }

        // Despedidas
        if (mensaje.includes('gracias') || mensaje.includes('chao') || mensaje.includes('adios') || mensaje.includes('adiós') || mensaje.includes('bye')) {
            return {
                texto: '¡De nada! 😊 Fue un placer ayudarte.<br><br>' +
                       'Si necesitas algo más, aquí estaré. ¡Que tengas un excelente día! 🌟',
                opciones: null
            };
        }

        // Respuesta por defecto
        return {
            texto: '🤔 Gracias por tu mensaje. ¿Puedo ayudarte con algo más específico?<br><br>' +
                   'Puedo ayudarte con:',
            opciones: [
                { texto: 'Productos y Servicios', icono: 'bi-box-seam', accion: 'productos' },
                { texto: 'Horarios de Atención', icono: 'bi-clock', accion: 'horarios' },
                { texto: 'Información de Envíos', icono: 'bi-truck', accion: 'envios' },
                { texto: 'Datos de Contacto', icono: 'bi-telephone', accion: 'contacto' }
            ]
        };
    }

    // ========== ANIMACIÓN DE SALIDA DE MENSAJE ==========
    const style = document.createElement('style');
    style.textContent = `
        @keyframes message-slide-out {
            to {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
        }
        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }
    `;
    document.head.appendChild(style);

    // ========== MENSAJE DE BIENVENIDA INICIAL ==========
    setTimeout(() => {
        if (!chatAbierto) {
            unreadBadge.style.animation = 'badge-bounce 1s ease infinite, badge-glow 2s ease infinite';
        }
    }, 3000);

    // Efecto glow para el badge
    const glowStyle = document.createElement('style');
    glowStyle.textContent = `
        @keyframes badge-glow {
            0%, 100% { box-shadow: 0 2px 8px rgba(255, 68, 68, 0.5); }
            50% { box-shadow: 0 4px 16px rgba(255, 68, 68, 0.8); }
        }
    `;
    document.head.appendChild(glowStyle);
</script>
</body>
</html>