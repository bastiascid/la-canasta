<?php
// Mock HTTP requests in WhatsAppService to avoid actual calls.
require_once '../api/whatsapp/BotStateMachine.php';
require_once '../api/db.php';

class MockWhatsAppService extends WhatsAppService {
    public function sendMessage($to, $text) {
        echo "[BOT -> $to]: $text\n";
        return true;
    }
}

// Override the waService in the statemachine
$machine = new BotStateMachine($pdo);
$reflection = new ReflectionClass($machine);
$property = $reflection->getProperty('waService');
$property->setAccessible(true);
$property->setValue($machine, new MockWhatsAppService());

$phone = '56912345678';
function simMsg($machine, $phone, $text, $id) {
    echo "\n[CLIENT -> BOT]: $text\n";
    $machine->processMessage($phone, $text, $id);
}

// Empezar de 0
$pdo->exec("DELETE FROM whatsapp_messages WHERE conversation_id IN (SELECT id FROM whatsapp_conversations WHERE phone_number = '$phone')");
$pdo->exec("DELETE FROM whatsapp_conversations WHERE phone_number = '$phone'");
$pdo->exec("DELETE FROM leads WHERE phone = '$phone'");

simMsg($machine, $phone, "Hola", "msg1");
simMsg($machine, $phone, "Juan Pérez", "msg2");
simMsg($machine, $phone, "1234567", "msg3"); // Invalido
simMsg($machine, $phone, "12345678-5", "msg4"); // Valido
simMsg($machine, $phone, "Minimarket El Sol", "msg5");
simMsg($machine, $phone, "Dueño", "msg6");
simMsg($machine, $phone, "O'Higgins", "msg7");
simMsg($machine, $phone, "Rancagua", "msg8");
simMsg($machine, $phone, "Quiero comprar por mayor", "msg9");
simMsg($machine, $phone, "Sí, correctos", "msg10");
simMsg($machine, $phone, "Hola de nuevo", "msg11");

echo "\n--- Fin del Test ---\n";
