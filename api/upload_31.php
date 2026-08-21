<?php
require_once 'db.php';

$products = [
    ['name' => 'Sucedáneo de Limón 250ml', 'category' => 'Sucedáneo de Limón'],
    ['name' => 'Sucedáneo de Limón 500ml', 'category' => 'Sucedáneo de Limón'],
    ['name' => 'Vinagre de Vino Blanco 250ml', 'category' => 'Vinagres'],
    ['name' => 'Vinagre de Vino Blanco 500ml', 'category' => 'Vinagres'],
    ['name' => 'Vinagre de Vino Rosado 250ml', 'category' => 'Vinagres'],
    ['name' => 'Vinagre de Vino Rosado 500ml', 'category' => 'Vinagres'],
    ['name' => 'Vinagre de Manzana 250ml', 'category' => 'Vinagres'],
    ['name' => 'Vinagre de Manzana 500ml', 'category' => 'Vinagres'],
    ['name' => 'Ketchup 100g', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Ají Salsa 100g', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Ají Crema 100g', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Ketchup 240g', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Mostaza 240g', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Ají Pebre 230g', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Ají Crema 240g', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Salsa de Soya 250ml', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Salsa de Soya 320ml', 'category' => 'Salsas y Aderezos'],
    ['name' => 'Esencia de Vainilla 115ml', 'category' => 'Esencias'],
    ['name' => 'Esencia de Vainilla 500ml', 'category' => 'Esencias'],
    ['name' => 'Aceituna Huasco 200g', 'category' => 'Aceitunas y Pickles'],
    ['name' => 'Cebolla Perla 200g', 'category' => 'Aceitunas y Pickles'],
    ['name' => 'Pepinillo 200g', 'category' => 'Aceitunas y Pickles'],
    ['name' => 'Pickles 200g', 'category' => 'Aceitunas y Pickles'],
    ['name' => 'Fideos Inst. Carne 85g', 'category' => 'Sopas y Fideos'],
    ['name' => 'Fideos Inst. Pollo 85g', 'category' => 'Sopas y Fideos'],
    ['name' => 'Fideos Inst. Verduras 85g', 'category' => 'Sopas y Fideos'],
    ['name' => 'Sopa Inst. Camarón 65g', 'category' => 'Sopas y Fideos'],
    ['name' => 'Sopa Inst. Carne 65g', 'category' => 'Sopas y Fideos'],
    ['name' => 'Sopa Inst. Pollo 65g', 'category' => 'Sopas y Fideos'],
    ['name' => 'Sopa Inst. Vegetales 65g', 'category' => 'Sopas y Fideos']
];

// Get Traverso brand id
$stmt = $pdo->query("SELECT id FROM brands WHERE name = 'Traverso' OR name = 'Mercado Nacional' LIMIT 1");
$brand_id = $stmt->fetchColumn();
if (!$brand_id) $brand_id = 1;

$inserted = 0;
foreach ($products as $p) {
    // Check if exists
    $check = $pdo->prepare("SELECT id FROM products WHERE name = ?");
    $check->execute([$p['name']]);
    if ($check->rowCount() == 0) {
        $insert = $pdo->prepare("INSERT INTO products (name, brand_id, category, description, image_url, featured, sort_order) VALUES (?, ?, ?, ?, ?, 0, 100)");
        $insert->execute([
            $p['name'],
            $brand_id,
            $p['category'],
            'Producto del catálogo Traverso.',
            'https://placehold.co/400x400/0f2c59/ffffff?text=Traverso'
        ]);
        $inserted++;
    }
}

// Delete the junk bulk products
$pdo->query("DELETE FROM products WHERE name LIKE 'Traverso Producto %' OR name LIKE 'Watts Producto %'");

echo "Success. Inserted $inserted real products. Cleaned up junk bulk products.";
?>
