<?php
// api/whatsapp/webhook.php
require_once '../db.php';
require_once 'BotStateMachine.php';
require_once 'WhatsAppService.php';

// Leer el .env si existe para obtener los tokens
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

$verify_token = getenv('WA_VERIFY_TOKEN') ?: 'DEFAULT_VERIFY_TOKEN';

// Manejar verificación del webhook (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['hub_mode']) && isset($_GET['hub_verify_token']) && isset($_GET['hub_challenge'])) {
        $mode = $_GET['hub_mode'];
        $token = $_GET['hub_verify_token'];
        $challenge = $_GET['hub_challenge'];

        if ($mode === 'subscribe' && $token === $verify_token) {
            http_response_code(200);
            echo $challenge;
            exit;
        } else {
            http_response_code(403);
            exit;
        }
    }
    http_response_code(400);
    exit;
}

// Manejar recepción de mensajes (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if ($data && isset($data['object']) && $data['object'] === 'whatsapp_business_account') {
        foreach ($data['entry'] as $entry) {
            foreach ($entry['changes'] as $change) {
                if ($change['value']['messaging_product'] === 'whatsapp' && isset($change['value']['messages'])) {
                    $message = $change['value']['messages'][0];
                    $contact = $change['value']['contacts'][0];
                    
                    $phone = $message['from'];
                    $text = isset($message['text']['body']) ? $message['text']['body'] : '';
                    $meta_msg_id = $message['id'];

                    // Procesar mensaje mediante la máquina de estados
                    $machine = new BotStateMachine($pdo);
                    $machine->processMessage($phone, $text, $meta_msg_id);
                }
            }
        }
        http_response_code(200);
        echo 'EVENT_RECEIVED';
    } else {
        http_response_code(404);
    }
}
