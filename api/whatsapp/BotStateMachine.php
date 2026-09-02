<?php
require_once 'CustomerService.php';
require_once 'WhatsAppService.php';

class BotStateMachine {
    private $pdo;
    private $customerService;
    private $waService;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->customerService = new CustomerService($pdo);
        $this->waService = new WhatsAppService();
    }

    private function getConversation($phone) {
        $stmt = $this->pdo->prepare("SELECT * FROM whatsapp_conversations WHERE phone_number = :phone ORDER BY id DESC LIMIT 1");
        $stmt->execute([':phone' => $phone]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function createConversation($phone) {
        $stmt = $this->pdo->prepare("INSERT INTO whatsapp_conversations (phone_number, status, bot_state, context_data) VALUES (:phone, 'BOT_ACTIVE', 'INICIO', '{}')");
        $stmt->execute([':phone' => $phone]);
        return $this->getConversation($phone);
    }

    private function saveMessage($convId, $dir, $text, $metaId = null) {
        // Idempotencia: Verificar si el meta_msg_id ya existe
        if ($metaId) {
            $stmt = $this->pdo->prepare("SELECT id FROM whatsapp_messages WHERE meta_message_id = :metaId LIMIT 1");
            $stmt->execute([':metaId' => $metaId]);
            if ($stmt->fetch()) return false; // Ya procesado
        }

        $stmt = $this->pdo->prepare("INSERT INTO whatsapp_messages (conversation_id, direction, content, meta_message_id) VALUES (:cid, :dir, :content, :metaId)");
        $stmt->execute([
            ':cid' => $convId,
            ':dir' => $dir,
            ':content' => $text,
            ':metaId' => $metaId
        ]);
        return true;
    }

    private function updateState($convId, $state, $contextData = null) {
        $sql = "UPDATE whatsapp_conversations SET bot_state = :state";
        $params = [':state' => $state, ':id' => $convId];
        
        if ($contextData !== null) {
            $sql .= ", context_data = :context";
            $params[':context'] = json_encode($contextData);
        }
        
        $sql .= " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    private function updateStatus($convId, $status) {
        $stmt = $this->pdo->prepare("UPDATE whatsapp_conversations SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $convId]);
    }

    private function isDerivationKeyword($text) {
        $keywords = ['ejecutivo', 'persona', 'asesor', 'asesora', 'vendedor', 'vendedora', 'hablar con alguien'];
        $text = strtolower($text);
        foreach ($keywords as $kw) {
            if (strpos($text, $kw) !== false) return true;
        }
        return false;
    }

    public function processMessage($phone, $text, $metaMsgId) {
        $conv = $this->getConversation($phone);
        
        if (!$conv) {
            $conv = $this->createConversation($phone);
        }

        // Evitar procesar duplicados
        if (!$this->saveMessage($conv['id'], 'CLIENT', $text, $metaMsgId)) {
            return; 
        }

        if ($conv['status'] === 'EXECUTIVE_ACTIVE') {
            // El bot no responde, solo guardamos el mensaje.
            return;
        }

        $context = json_decode($conv['context_data'], true) ?: [];
        $state = $conv['bot_state'];
        $reply = "";
        $newState = $state;

        if ($this->isDerivationKeyword($text) && $state !== 'DERIVAR_EJECUTIVO' && $state !== 'INICIO') {
            $reply = "Entendido. Derivaremos tu solicitud directamente a un ejecutivo comercial de La Canasta para que pueda ayudarte.";
            $newState = 'DERIVAR_EJECUTIVO';
        } else {
            switch ($state) {
                case 'INICIO':
                    $reply = "¡Hola! Somos La Canasta Comercializadora y Distribuidora.\n\nTe ayudaremos a gestionar tu solicitud y te pondremos en contacto con un ejecutivo comercial.\n\nPara comenzar, ¿cuál es tu nombre?";
                    $newState = 'IDENTIFICAR_NOMBRE';
                    break;
                case 'IDENTIFICAR_NOMBRE':
                    $context['name'] = trim($text);
                    $reply = "¡Perfecto! ¿Cuál es tu nombre? (Ah, disculpa, ya me lo dijiste). Gracias, {$context['name']}. Para continuar, indícanos tu RUT."; // Corregido el flujo
                    $reply = "Gracias, {$context['name']}. Para continuar, indícanos tu RUT.";
                    $newState = 'SOLICITAR_RUT';
                    break;
                case 'SOLICITAR_RUT':
                    if (!$this->customerService->validateRut($text)) {
                        $reply = "El RUT ingresado no parece válido. Por favor, revísalo e ingrésalo nuevamente.";
                    } else {
                        $context['rut'] = $this->customerService->cleanRut($text);
                        $lead = $this->customerService->findByPhoneOrRut($phone, $context['rut']);
                        
                        if ($lead && !empty($lead['company'])) {
                            $reply = "Encontramos tus datos como cliente de La Canasta.\n\nPara ayudarte mejor, cuéntanos ¿qué necesitas realizar? (Ej: Cotizar productos, Realizar un pedido, etc.)";
                            $newState = 'SOLICITAR_NECESIDAD_CLIENTE';
                        } else {
                            $reply = "¡Gracias! Vemos que aún no tienes registro completo como cliente de La Canasta.\n\nPara derivarte correctamente con nuestro equipo comercial, necesitamos algunos datos.\n\n¿Cuál es el nombre de tu negocio o empresa?";
                            $newState = 'SOLICITAR_EMPRESA';
                        }
                    }
                    break;
                case 'SOLICITAR_NECESIDAD_CLIENTE':
                    $context['comments'] = trim($text);
                    $reply = "¡Perfecto! Hemos registrado tu solicitud.\n\nAhora derivaremos tu caso a un ejecutivo comercial de La Canasta para que pueda ayudarte directamente.\n\nPor favor, espera un momento.";
                    $newState = 'DERIVAR_EJECUTIVO';
                    break;
                case 'SOLICITAR_EMPRESA':
                    $context['company'] = trim($text);
                    $reply = "¿Cuál es tu cargo o relación con el negocio? (Ej: Dueño/a, Administrador/a)";
                    $newState = 'SOLICITAR_CARGO';
                    break;
                case 'SOLICITAR_CARGO':
                    $context['role'] = trim($text);
                    $reply = "¿En qué Región te encuentras? (Ej: Región de O'Higgins)";
                    $newState = 'SOLICITAR_REGION';
                    break;
                case 'SOLICITAR_REGION':
                    $context['region'] = trim($text);
                    $reply = "¿En qué Comuna se ubica tu negocio?";
                    $newState = 'SOLICITAR_COMUNA';
                    break;
                case 'SOLICITAR_COMUNA':
                    $context['comuna'] = trim($text);
                    $reply = "Cuéntanos brevemente qué necesitas. Por ejemplo, qué productos te interesan, si quieres realizar un pedido o si deseas conocer nuestra oferta comercial.";
                    $newState = 'SOLICITAR_NECESIDAD_NUEVO';
                    break;
                case 'SOLICITAR_NECESIDAD_NUEVO':
                    $context['comments'] = trim($text);
                    $reply = "Perfecto. Antes de derivarte con nuestro equipo, revisemos tus datos:\n\nNombre: {$context['name']}\nRUT: {$context['rut']}\nNegocio: {$context['company']}\nCargo: {$context['role']}\nRegión: {$context['region']}\nComuna: {$context['comuna']}\n\nSolicitud: {$context['comments']}\n\n¿Los datos están correctos? (Responde Sí, o 'Quiero corregirlos')";
                    $newState = 'CONFIRMAR_DATOS';
                    break;
                case 'CONFIRMAR_DATOS':
                    $resp = strtolower(trim($text));
                    if (strpos($resp, 'no') !== false || strpos($resp, 'corregir') !== false) {
                        $reply = "Entendido, empecemos de nuevo. ¿Cuál es tu nombre?";
                        $newState = 'IDENTIFICAR_NOMBRE';
                        $context = []; // Reset
                    } else {
                        $reply = "¡Perfecto! Hemos registrado tus datos.\n\nAhora derivaremos tu solicitud a un ejecutivo comercial de La Canasta para que pueda ayudarte directamente.\n\nPor favor, espera un momento.";
                        $newState = 'DERIVAR_EJECUTIVO';
                    }
                    break;
                case 'DERIVAR_EJECUTIVO':
                case 'PENDIENTE_EJECUTIVO':
                    // Ya está derivado, no hacemos nada a menos que digan algo, podemos auto-responder.
                    $reply = "Tu solicitud ya está en cola. Un ejecutivo te responderá a la brevedad.";
                    $newState = 'PENDIENTE_EJECUTIVO';
                    break;
                default:
                    $reply = "No entiendo ese comando, pero un ejecutivo se contactará contigo pronto.";
                    $newState = $state;
            }
        }

        // Si el estado es DERIVAR_EJECUTIVO, guardar en leads
        if ($newState === 'DERIVAR_EJECUTIVO') {
            $context['phone'] = $phone;
            $leadId = $this->customerService->createOrUpdateLead($context);
            
            $stmt = $this->pdo->prepare("UPDATE whatsapp_conversations SET lead_id = :lid, status = 'PENDIENTE_EJECUTIVO' WHERE id = :id");
            $stmt->execute([':lid' => $leadId, ':id' => $conv['id']]);
            $newState = 'PENDIENTE_EJECUTIVO';
        }

        $this->updateState($conv['id'], $newState, $context);

        if (!empty($reply)) {
            $this->waService->sendMessage($phone, $reply);
            $this->saveMessage($conv['id'], 'BOT', $reply);
        }
    }
}
