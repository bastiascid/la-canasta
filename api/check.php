<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT count(*) FROM products WHERE name LIKE 'Watts Producto%' OR name LIKE 'Traverso Producto%'");
echo "Count: " . $stmt->fetchColumn();
?>
