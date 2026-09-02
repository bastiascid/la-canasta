<?php
// api/whatsapp_setup.php
require_once 'db.php';
header('Content-Type: application/json');

$passcode = isset($_GET['passcode']) ? $_GET['passcode'] : '';
if ($passcode !== 'admin123') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado.']);
    exit;
}

try {
    // 1. Modificar tabla leads para permitir email NULL
    $pdo->exec("ALTER TABLE leads MODIFY COLUMN email VARCHAR(100) NULL");

    // 2. Crear tabla whatsapp_conversations
    $pdo->exec("CREATE TABLE IF NOT EXISTS whatsapp_conversations (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT DEFAULT NULL,
        `phone_number` VARCHAR(30) NOT NULL UNIQUE,
        `status` VARCHAR(50) DEFAULT 'BOT_ACTIVE',
        `bot_state` VARCHAR(50) DEFAULT 'INICIO',
        `context_data` TEXT,
        `executive_id` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`lead_id`) REFERENCES leads(`id`) ON DELETE SET NULL
    )");

    // 3. Crear tabla whatsapp_messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS whatsapp_messages (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `conversation_id` INT NOT NULL,
        `direction` VARCHAR(20) NOT NULL, -- 'CLIENT', 'BOT', 'EXECUTIVE'
        `content` TEXT NOT NULL,
        `message_type` VARCHAR(30) DEFAULT 'text',
        `meta_message_id` VARCHAR(150),
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`conversation_id`) REFERENCES whatsapp_conversations(`id`) ON DELETE CASCADE
    )");

    echo json_encode(['status' => 'success', 'message' => 'Base de datos WhatsApp configurada correctamente.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
