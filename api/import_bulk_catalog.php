<?php
require_once 'db.php';

// Verificación básica
if (!isset($_GET['passcode']) || $_GET['passcode'] !== 'admin123') {
    die("Acceso denegado.");
}

$json_file = '../assets/catalogos/productos_masivos.json';
if (!file_exists($json_file)) {
    die("No se encontró el archivo JSON masivo.");
}

$json_data = file_get_contents($json_file);
$products = json_decode($json_data, true);

if (!$products) {
    die("Error al decodificar JSON.");
}

// Obtener brands para foreign keys
$stmt_brand = $pdo->query("SELECT id, name FROM brands");
$brands = [];
while ($row = $stmt_brand->fetch()) {
    $brands[$row['name']] = $row['id'];
}

// Fallback por si no existen, usaremos el primero o null
$default_brand = !empty($brands) ? reset($brands) : 1;

$count_new = 0;
$count_existing = 0;

foreach ($products as $prod) {
    // Determinar brand_id
    $brand_id = $default_brand;
    if (strpos($prod['category'], "Watt's") !== false && isset($brands["Watt's"])) {
        $brand_id = $brands["Watt's"];
    } elseif (strpos($prod['category'], "Traverso") !== false && isset($brands["Mercado Nacional"])) {
        // Asignamos Traverso a Mercado Nacional por mientras si no existe marca Traverso
        $brand_id = isset($brands["Traverso"]) ? $brands["Traverso"] : $brands["Mercado Nacional"];
    }
    
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE image_url = :image");
    $stmt->execute([':image' => $prod['image']]);
    
    if ($stmt->rowCount() == 0) {
        $insert = $pdo->prepare("INSERT INTO products (name, brand_id, description, image_url, category, featured, sort_order) VALUES (:name, :brand_id, :description, :image, :category, 0, 100)");
        $insert->execute([
            ':name' => $prod['name'],
            ':brand_id' => $brand_id,
            ':description' => $prod['description'],
            ':image' => $prod['image'],
            ':category' => $prod['category']
        ]);
        $count_new++;
    } else {
        $count_existing++;
    }
}

echo "Proceso completado. Nuevos productos: $count_new. Productos ya existentes (ignorados): $count_existing.";
?>
