<?php
// api/temp_backup.php
// Temporary script to dump database tables for backup. Will be deleted after backup.

require_once 'db.php';

header('Content-Type: application/json');

$passcode = isset($_GET['passcode']) ? $_GET['passcode'] : '';
if ($passcode !== 'admin123') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado.'
    ]);
    exit;
}

try {
    $tables = ['settings', 'brands', 'products', 'leads', 'coverage', 'offers', 'partners', 'sub_brands'];
    $backup = [];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $backup[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $pe) {
            $backup[$table] = [];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $backup
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
