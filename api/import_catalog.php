<?php
// api/import_catalog.php
// Seeding new products from Mercado Nacional and Watt's catalogs

require_once 'db.php';

header('Content-Type: application/json');

// Security passcode verification
$passcode = isset($_GET['passcode']) ? $_GET['passcode'] : '';
if ($passcode !== 'admin123') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado. Código de acceso inválido.'
    ]);
    exit;
}

try {
    // Get brand IDs dynamically
    $stmt_brand = $pdo->query("SELECT id, name FROM brands");
    $brands = [];
    while ($row = $stmt_brand->fetch()) {
        $brands[$row['name']] = $row['id'];
    }

    if (!isset($brands['Mercado Nacional']) || !isset($brands['Watt\'s'])) {
        throw new Exception("Marcas requeridas 'Mercado Nacional' o 'Watt's' no se encuentran en la base de datos.");
    }

    $mn_id = $brands['Mercado Nacional'];
    $watts_id = $brands['Watt\'s'];

    // Product definitions to import
    $new_products = [
        // Brand: Mercado Nacional (Turbo products)
        [
            'name' => 'Turbo Plus Frambuesa 20g (Caja 10 un)',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_plus_frambuesa.jpg',
            'description' => 'Bebida instantánea en polvo sabor frambuesa, cada sobre rinde 2 litros.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 10
        ],
        [
            'name' => 'Turbo Iced Tea Limón 25g (Caja 12 un)',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_iced_tea_limon.jpg',
            'description' => 'Té helado instantáneo sabor limón con vitamina C, cada sobre rinde 1 litro.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 11
        ],
        [
            'name' => 'Turbo Zero Naranja 10g (Caja 10 un)',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_zero_naranja.jpg',
            'description' => 'Bebida instantánea sabor naranja libre de azúcar, rinde 2 litros.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 12
        ],
        [
            'name' => 'Turbo Benny Chocolate Bolsa 200g',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_benny_200g.jpg',
            'description' => 'Modificador de leche sabor chocolate enriquecido con vitaminas y hierro, rinde 20 porciones.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 13
        ],
        [
            'name' => 'Turbo Benny Zero Chocolate Bolsa 20g',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_benny_zero_20g.jpg',
            'description' => 'Modificador de leche sabor chocolate libre de azúcar, libre de lactosa.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 14
        ],
        [
            'name' => 'Turbo Flan Vainilla Estuche 50g',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_flan_vainilla.jpg',
            'description' => 'Mezcla en polvo para preparar flan sabor vainilla, rinde 5 porciones.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 15
        ],
        [
            'name' => 'Turbo Gelatina Frutilla Estuche 40g',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_gelatina_frutilla.jpg',
            'description' => 'Gelatina en polvo sabor frutilla con stevia, rinde 5 porciones.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 16
        ],
        [
            'name' => 'Turbo Gelatina Sin Sabor Estuche 30g',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_gelatina_sin_sabor.jpg',
            'description' => 'Gelatina en polvo sin sabor ideal para preparar postres y recetas.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 17
        ],
        [
            'name' => 'Turbo Flan Zero Vainilla Estuche 20g',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_flan_zero.jpg',
            'description' => 'Flan en polvo sabor vainilla libre de azúcar, rinde 10 porciones.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 18
        ],
        [
            'name' => 'Turbo Gelatina Zero Frutilla Estuche 22g',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_gelatina_zero.jpg',
            'description' => 'Gelatina en polvo sabor frutilla libre de azúcar, rinde 10 porciones.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 19
        ],
        [
            'name' => 'Turbo Energy Lata 473ml (Pack 6 un)',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_energy_can.jpg',
            'description' => 'Bebida energética con taurina y vitaminas del complejo B, formato lata.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 20
        ],
        [
            'name' => 'Turbo Iced Tea Durazno Lata 473ml (Pack 6 un)',
            'brand_id' => $mn_id,
            'image_url' => 'assets/productos/turbo_iced_tea_can.jpg',
            'description' => 'Té helado líquido sabor durazno refrescante en formato lata.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 21
        ],

        // Brand: Watt's
        [
            'name' => 'Jugo Watt\'s Selección Naranja 1.5L (Caja 6 un)',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/jugo_watts_naranja.jpg',
            'description' => 'Jugo 100% de naranja exprimido, sin agua, sin azúcar añadida y sin preservantes.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 10
        ],
        [
            'name' => 'Jugo Watt\'s Selección Piña 1.5L (Caja 6 un)',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/jugo_watts_pina.jpg',
            'description' => 'Jugo 100% de piña exprimido de fruta seleccionada, sin preservantes ni colorantes.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 11
        ],
        [
            'name' => 'Néctar Watt\'s Selección Frambuesa 1L (Caja 12 un)',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/nectar_watts_frambuesa.jpg',
            'description' => 'Néctar de fruta natural sabor frambuesa en envase reciclable de 1 litro.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 12
        ],
        [
            'name' => 'Frugo Fresh Frutilla 1.75L (Caja 6 un)',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/frugo_fresh_frutilla.jpg',
            'description' => 'Néctar refrescante de frutas sabor frutilla con más contenido de fruta.',
            'category' => 'Bebidas y Postres',
            'featured' => 0,
            'sort_order' => 13
        ],
        [
            'name' => 'Frugo Fresh Piña 1.75L (Caja 6 un)',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/frugo_fresh_pina.jpg',
            'description' => 'Néctar refrescante de frutas sabor piña, ideal para el abastecimiento comercial.',
            'category' => 'Bebidas y Postres',
            'featured' => 1,
            'sort_order' => 14
        ],
        [
            'name' => 'Crema para Batir Loncoleche 500ml (Caja 12 un)',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/loncoleche_crema.jpg',
            'description' => 'Crema de leche UHT para batir con 35% materia grasa, elaborada con leche natural.',
            'category' => 'Lácteos y Quesos',
            'featured' => 1,
            'sort_order' => 15
        ],
        [
            'name' => 'Queso Cheddar Laminado Calo 1.92kg',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/calo_cheddar_slices.jpg',
            'description' => 'Queso fundido procesado sabor cheddar laminado (160 láminas), formato industrial.',
            'category' => 'Lácteos y Quesos',
            'featured' => 1,
            'sort_order' => 16
        ],
        [
            'name' => 'Piña en Rodajas Wasil 3kg (Lata)',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/wasil_pina_slices.jpg',
            'description' => 'Fruta seleccionada en conserva de piñas en rodajas, sin preservantes ni colorantes.',
            'category' => 'Conservas',
            'featured' => 0,
            'sort_order' => 17
        ],
        [
            'name' => 'Manjar Artesanal Loncoleche Balde 5kg',
            'brand_id' => $watts_id,
            'image_url' => 'assets/productos/loncoleche_manjar.jpg',
            'description' => 'Balde de manjar artesanal de receta tradicional, fabricado en Osorno.',
            'category' => 'Abarrotes',
            'featured' => 1,
            'sort_order' => 18
        ]
    ];

    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name = ?");
    $stmt_insert = $pdo->prepare("INSERT INTO products (name, brand_id, image_url, description, category, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $inserted_count = 0;
    $skipped_count = 0;

    foreach ($new_products as $p) {
        $stmt_check->execute([$p['name']]);
        if ($stmt_check->fetchColumn() == 0) {
            $stmt_insert->execute([
                $p['name'],
                $p['brand_id'],
                $p['image_url'],
                $p['description'],
                $p['category'],
                $p['featured'],
                $p['sort_order']
            ]);
            $inserted_count++;
        } else {
            $skipped_count++;
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Proceso de importación de catálogo completado.',
        'data' => [
            'inserted' => $inserted_count,
            'skipped' => $skipped_count
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error durante la importación: ' . $e->getMessage()
    ]);
}
