<?php
// api/backup_db.php
require_once 'db.php';
require_once 'auth.php';

require_admin_auth();

header('Content-Type: application/json');

try {
    $backup = [
        'settings' => [],
        'brands' => [],
        'products' => [],
        'leads' => []
    ];
    
    // Fetch settings
    $stmt = $pdo->query("SELECT * FROM settings");
    $backup['settings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch brands
    $stmt = $pdo->query("SELECT * FROM brands");
    $backup['brands'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch products
    $stmt = $pdo->query("SELECT * FROM products");
    $backup['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch leads
    $stmt = $pdo->query("SELECT * FROM leads");
    $backup['leads'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success',
        'data' => $backup
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error backing up database: ' . $e->getMessage()
    ]);
}
