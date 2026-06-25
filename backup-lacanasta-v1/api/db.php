<?php
// api/db.php
// Database configuration and connection helper

$db_host = 'localhost';
$db_name = 'cla117198_canasta_db';
$db_user = 'cla117198_canasta_usr';
$db_pass = 'CanastaDb2026_SecurePass!';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de conexion a la base de datos.'
    ]);
    exit;
}
