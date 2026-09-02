<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';
if (!isset($_GET['passcode']) || $_GET['passcode'] !== 'admin123') die("Acceso denegado.");

$json_data = file_get_contents('new_db.json');
if (!$json_data) {
    die("Error leyendo new_db.json");
}

$products = json_decode($json_data, true);
if (!$products) die("Error decodificando JSON");

try {
    $pdo->beginTransaction();
    
    // Use DELETE instead of TRUNCATE to avoid permission issues
    $pdo->exec("DELETE FROM products WHERE id > 4;");
    
    $stmt = $pdo->prepare("INSERT INTO products (id, name, brand_id, description, image_url, category, featured, sort_order) VALUES (:id, :name, :brand_id, :description, :image_url, :category, :featured, :sort_order)");
    
    $count = 0;
    foreach ($products as $p) {
        if ($p['id'] <= 4) continue; // skip the ones we didn't delete
        $stmt->execute([
            ':id' => $p['id'],
            ':name' => $p['name'],
            ':brand_id' => $p['brand_id'],
            ':description' => $p['description'],
            ':image_url' => $p['image_url'],
            ':category' => $p['category'],
            ':featured' => $p['featured'] ?? 0,
            ':sort_order' => $p['sort_order'] ?? 100
        ]);
        $count++;
    }
    
    $pdo->commit();
    echo "Sincronizacion completa. $count productos insertados correctamente.";
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error: " . $e->getMessage());
}
?>
