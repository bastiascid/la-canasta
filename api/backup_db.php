<?php
require_once 'db.php';
if (!isset($_GET['passcode']) || $_GET['passcode'] !== 'admin123') die("Acceso denegado.");
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('../assets/catalogos/db_backup_full.json', json_encode($products, JSON_PRETTY_PRINT));
echo "Backup saved.";
?>
