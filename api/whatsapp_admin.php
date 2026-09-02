<?php
require_once 'db.php';
require_once 'auth.php'; // Ensure admin auth

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$action = $_GET['action'] ?? 'list';

try {
    if ($action === 'list') {
        $stmt = $pdo->query("
            SELECT w.*, l.name, l.company, l.rut, l.region, l.comuna, l.comments as lead_comments 
            FROM whatsapp_conversations w 
            LEFT JOIN leads l ON w.lead_id = l.id 
            ORDER BY w.updated_at DESC
        ");
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
    } 
    elseif ($action === 'messages') {
        $convId = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM whatsapp_messages WHERE conversation_id = :cid ORDER BY created_at ASC");
        $stmt->execute([':cid' => $convId]);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
    }
    elseif ($action === 'take') {
        $convId = $_POST['id'] ?? 0;
        // In real world, executive_id would be the logged in admin's ID
        $stmt = $pdo->prepare("UPDATE whatsapp_conversations SET status = 'EXECUTIVE_ACTIVE', executive_id = 1 WHERE id = :id");
        $stmt->execute([':id' => $convId]);
        echo json_encode(['status' => 'success', 'message' => 'Conversación tomada por ejecutivo.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
