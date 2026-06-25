<?php
// api/setup.php
// Setup database tables and load default data

require_once 'db.php';

header('Content-Type: application/json');

try {
    // 1. Create Tables
    $queries = [
        "CREATE TABLE IF NOT EXISTS settings (
            `key` VARCHAR(50) PRIMARY KEY,
            `value` TEXT NOT NULL
        )",
        "CREATE TABLE IF NOT EXISTS brands (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `logo_url` TEXT,
            `image_url` TEXT,
            `description` TEXT,
            `status` VARCHAR(20) DEFAULT 'Activa',
            `sort_order` INT DEFAULT 0,
            `official_url` VARCHAR(255)
        )",
        "CREATE TABLE IF NOT EXISTS products (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) NOT NULL,
            `brand_id` INT NOT NULL,
            `image_url` TEXT,
            `description` TEXT,
            `category` VARCHAR(100),
            `featured` TINYINT DEFAULT 0,
            `sort_order` INT DEFAULT 0,
            FOREIGN KEY (`brand_id`) REFERENCES brands(`id`) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS leads (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `company` VARCHAR(100),
            `role` VARCHAR(100),
            `phone` VARCHAR(30),
            `email` VARCHAR(100) NOT NULL,
            `region` VARCHAR(100),
            `comments` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($queries as $q) {
        $pdo->exec($q);
    }

    // 2. Insert Default Settings if empty
    $check_settings = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($check_settings == 0) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)");
        $stmt->execute(['whatsapp_enabled', '0']);
        $stmt->execute(['whatsapp_number', '+56 9 4256 7472']);
        $stmt->execute(['logo_url', 'assets/canasta-logo.png']);
    }

    // 3. Insert Default Brands if empty
    $check_brands = $pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();
    if ($check_brands == 0) {
        $brands = [
            [
                'Angelmo',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Angelmo',
                'https://images.unsplash.com/photo-1486299267070-83823f5448dd?w=600&auto=format&fit=crop&q=60',
                'Marca lider en quesos y lacteos tradicionales con el sabor del sur de Chile.',
                'Activa',
                1,
                'https://www.angelmo.cl'
            ],
            [
                'Iansa',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Iansa',
                'https://images.unsplash.com/photo-1581798459219-318e76ae1db8?w=600&auto=format&fit=crop&q=60',
                'Lider nacional en azucar granulada, endulzantes y productos derivados de primera calidad.',
                'Activa',
                2,
                'https://www.empresasiansa.cl'
            ],
            [
                'Watt\'s',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Watt%27s',
                'https://images.unsplash.com/photo-1610970881699-44a5587caaec?w=600&auto=format&fit=crop&q=60',
                'Tradicion en jugos, aceites, mermeladas y alimentos procesados para la familia chilena.',
                'Activa',
                3,
                'https://www.watts.cl'
            ],
            [
                'Traverso',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Traverso',
                'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=60',
                'Condimentos, vinagres, aderezos y salsas que complementan las comidas en cada hogar.',
                'Activa',
                4,
                'https://www.traverso.cl'
            ],
            [
                'Mercado Nacional',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Mercado+Nacional',
                'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&auto=format&fit=crop&q=60',
                'Seleccion de abarrotes de alta rotacion directo de productores agricolas nacionales.',
                'Activa',
                5,
                '#'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO brands (`name`, `logo_url`, `image_url`, `description`, `status`, `sort_order`, `official_url`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($brands as $b) {
            $stmt->execute($b);
        }

        // Get brand IDs to insert default products
        $brand_ids = [];
        $res = $pdo->query("SELECT id, name FROM brands")->fetchAll();
        foreach ($res as $row) {
            $brand_ids[$row['name']] = $row['id'];
        }

        // 4. Insert Default Products if empty
        $check_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
        if ($check_products == 0) {
            $products = [
                // Angelmo
                ['Queso Mantecoso Colun (Pieza ~3kg)', $brand_ids['Angelmo'], 'https://images.unsplash.com/photo-1486299267070-83823f5448dd?w=600&auto=format&fit=crop&q=60', 'Queso mantecoso tradicional madurado, textura suave y excelente sabor.', 'Lácteos y Quesos', 1, 1],
                ['Queso laminado Angelmo (Caja 20 un)', $brand_ids['Angelmo'], 'https://images.unsplash.com/photo-1528256447608-f495146057e5?w=600&auto=format&fit=crop&q=60', 'Láminas de queso ideales para sándwiches y locales de comida rápida.', 'Lácteos y Quesos', 0, 2],
                // Iansa
                ['Azúcar Granulada Iansa (Saco 25kg)', $brand_ids['Iansa'], 'https://images.unsplash.com/photo-1581798459219-318e76ae1db8?w=600&auto=format&fit=crop&q=60', 'Saco industrial de azúcar granulada blanca, indispensable en panaderías y almacenes.', 'Abarrotes', 1, 1],
                ['Azúcar Granulada Iansa 1kg (Fardo 10 un)', $brand_ids['Iansa'], 'https://images.unsplash.com/photo-1581798459219-318e76ae1db8?w=600&auto=format&fit=crop&q=60', 'Formato retail fardo para abastecimiento directo de góndolas.', 'Abarrotes', 0, 2],
                // Watt's
                ['Aceite Vegetal Belmont 1L (Caja 12 un)', $brand_ids['Watt\'s'], 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=60', 'Aceite 100% vegetal para todo tipo de cocinas, formato mayorista.', 'Aceites', 1, 1],
                ['Néctar Watt\'s Durazno 1.5L (Caja 6 un)', $brand_ids['Watt\'s'], 'https://images.unsplash.com/photo-1610970881699-44a5587caaec?w=600&auto=format&fit=crop&q=60', 'Jugos y néctares de fruta natural, alto nivel de ventas en minimarkets.', 'Bebidas y Jugos', 0, 2],
                // Traverso
                ['Vinagre de Manzana Traverso 1L (Caja 12 un)', $brand_ids['Traverso'], 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=60', 'Vinagre de manzana puro, ideal para ensaladas y conservas.', 'Condimentos', 1, 1],
                ['Salsa de Tomate Traverso 200g (Caja 24 un)', $brand_ids['Traverso'], 'https://images.unsplash.com/photo-1533560224820-f3d1419e2c36?w=600&auto=format&fit=crop&q=60', 'Salsa de tomates clásica lista para servir.', 'Abarrotes', 0, 2],
                // Mercado Nacional
                ['Harina de Trigo Selecta Saco 25kg', $brand_ids['Mercado Nacional'], 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&auto=format&fit=crop&q=60', 'Harina refinada especial sin polvos de hornear, formato industrial.', 'Abarrotes', 1, 1],
                ['Arroz Grado 1 Mercado Nacional (Saco 20kg)', $brand_ids['Mercado Nacional'], 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop&q=60', 'Arroz grano largo de alta calidad, óptimo rendimiento comercial.', 'Abarrotes', 0, 2]
            ];

            $stmt = $pdo->prepare("INSERT INTO products (`name`, `brand_id`, `image_url`, `description`, `category`, `featured`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($products as $p) {
                $stmt->execute($p);
            }
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Tablas de la base de datos creadas e inicializadas con exito.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error durante el setup de base de datos: ' . $e->getMessage()
    ]);
}
