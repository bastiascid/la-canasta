<?php
// api/setup.php
// Setup database tables and load default data (Strategic Redesign Upgrade)

require_once 'db.php';

header('Content-Type: application/json');

// Proteccion de ejecucion: requiere codigo de acceso
$passcode = isset($_GET['passcode']) ? $_GET['passcode'] : '';
if ($passcode !== 'admin123') {
    http_response_code(403);
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso denegado. Se requiere el codigo de acceso correcto para ejecutar la configuracion.'
    ]);
    exit;
}

try {
    // 1. Create Core Tables
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
            `origin` VARCHAR(100) DEFAULT 'Formulario General',
            `status` VARCHAR(30) DEFAULT 'Nuevo',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS coverage (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `region` VARCHAR(100) DEFAULT \"Región de O'Higgins\",
            `sort_order` INT DEFAULT 0
        )",
        "CREATE TABLE IF NOT EXISTS offers (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(150) NOT NULL,
            `image_url` TEXT,
            `description` TEXT,
            `link_url` VARCHAR(255) DEFAULT '#',
            `status` VARCHAR(20) DEFAULT 'Activa',
            `type` VARCHAR(50) DEFAULT 'Campañas comerciales',
            `sort_order` INT DEFAULT 0,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS partners (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `logo_url` TEXT NOT NULL,
            `description` TEXT,
            `link_url` VARCHAR(255) DEFAULT '#',
            `sort_order` INT DEFAULT 0,
            `status` VARCHAR(20) DEFAULT 'Activo'
        )",
        "CREATE TABLE IF NOT EXISTS sub_brands (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(100) NOT NULL,
            `logo_url` TEXT NOT NULL,
            `sort_order` INT DEFAULT 0,
            `status` VARCHAR(20) DEFAULT 'Activo'
        )"
    ];

    foreach ($queries as $q) {
        $pdo->exec($q);
    }

    // 2. ALTER tables safely
    try {
        $pdo->query("SELECT history FROM brands LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE brands ADD COLUMN history TEXT");
    }

    try {
        $pdo->query("SELECT slug FROM brands LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE brands ADD COLUMN slug VARCHAR(100) UNIQUE");
    }

    try {
        $pdo->query("SELECT origin FROM leads LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE leads ADD COLUMN origin VARCHAR(100) DEFAULT 'Formulario General'");
    }

    try {
        $pdo->query("SELECT status FROM leads LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE leads ADD COLUMN status VARCHAR(30) DEFAULT 'Nuevo'");
    }

    try {
        $pdo->query("SELECT type FROM offers LIMIT 1");
    } catch (PDOException $e) {
        $pdo->exec("ALTER TABLE offers ADD COLUMN type VARCHAR(50) DEFAULT 'Campañas comerciales'");
    }

    // 3. Insert Default Settings if empty
    $check_settings = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
    if ($check_settings == 0) {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)");
        $stmt->execute(['whatsapp_enabled', '0']);
        $stmt->execute(['whatsapp_number', '+56 9 4256 7472']);
        $stmt->execute(['logo_url', 'assets/canasta-logo.png']);
    }

    // 4. Insert Default Brands if empty
    $check_brands = $pdo->query("SELECT COUNT(*) FROM brands")->fetchColumn();
    if ($check_brands == 0) {
        $brands = [
            [
                'Angelmo',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Angelmo',
                'https://images.unsplash.com/photo-1486299267070-83823f5448dd?w=600&auto=format&fit=crop&q=60',
                'Marca líder en quesos y lácteos tradicionales con el sabor del sur de Chile.',
                'Activa',
                1,
                'https://www.angelmofoods.cl/es'
            ],
            [
                'Iansa',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Iansa',
                'https://images.unsplash.com/photo-1581798459219-318e76ae1db8?w=600&auto=format&fit=crop&q=60',
                'Líder nacional en azúcar granulada, endulzantes y productos de origen natural de primera calidad.',
                'Activa',
                2,
                'https://empresasiansa.cl/'
            ],
            [
                'Watt\'s',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Watt%27s',
                'https://images.unsplash.com/photo-1610970881699-44a5587caaec?w=600&auto=format&fit=crop&q=60',
                'Tradición en jugos, aceites, mermeladas y alimentos procesados para la familia chilena.',
                'Activa',
                3,
                'https://www.watts.cl/'
            ],
            [
                'Traverso',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Traverso',
                'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=60',
                'Condimentos, vinagres, aderezos y salsas que complementan las comidas en cada hogar.',
                'Activa',
                4,
                'https://www.traverso.cl/'
            ],
            [
                'Mercado Nacional',
                'https://placehold.co/120x80/0f2c59/ffffff?text=Mercado+Nacional',
                'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=600&auto=format&fit=crop&q=60',
                'Selección de abarrotes de alta rotación directo de productores agrícolas nacionales.',
                'Activa',
                5,
                '#'
            ]
        ];

        $stmt = $pdo->prepare("INSERT INTO brands (`name`, `logo_url`, `image_url`, `description`, `status`, `sort_order`, `official_url`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($brands as $b) {
            $stmt->execute($b);
        }
    }

    // Seed history and slug for default/existing brands
    $brand_updates = [
        'Angelmo' => [
            'slug' => 'angelmo',
            'history' => 'Fundada en la década de 1990 en el sur de Chile, la marca Angelmo nació con la misión de llevar la frescura del mar y la tradición láctea de la Región de Los Lagos a todo el territorio nacional. Especializada en la maduración de quesos mantecosos tradicionales y conservas marinas selectas, Angelmo se ha posicionado en el canal tradicional como sinónimo de sabor sureño auténtico, calidad artesanal y confianza comercial.'
        ],
        'Iansa' => [
            'slug' => 'iansa',
            'history' => 'Con más de 70 años de trayectoria, Empresas Iansa es un pilar fundamental en la mesa de los chilenos. Desde sus inicios procesando remolacha azucarera, la marca ha evolucionado hasta convertirse en el principal proveedor nacional de azúcares granuladas de alta pureza, endulzantes, legumbres y alimento animal. Su compromiso con la agricultura del país asegura productos de primera calidad, ideales para panaderías, pastelerías y el comercio mayorista.'
        ],
        'Watt\'s' => [
            'slug' => 'watts',
            'history' => 'Watt\'s es una de las marcas de alimentos más consolidadas de Chile, con más de 80 años de historia entregando nutrición y sabor. Desde aceites vegetales Belmont hasta mermeladas y jugos de fruta natural, Watt\'s representa la tradición y la calidad en el hogar chileno. Su amplio portafolio de productos de alta rotación lo convierte en un socio indispensable para maximizar las ventas de cualquier local de barrio.'
        ],
        'Traverso' => [
            'slug' => 'traverso',
            'history' => 'Traverso S.A. es una empresa familiar chilena fundada a comienzos del siglo XX, reconocida por su excelencia en la elaboración de vinagres de manzana y vino, jugos de limón, salsas de tomate y encurtidos. A lo largo de las décadas, Traverso ha sabido conjugar recetas tradicionales con tecnología de punta para entregar aderezos premium indispensables en almacenes y cocinas de todo el país.'
        ],
        'Mercado Nacional' => [
            'slug' => 'mercado-nacional',
            'history' => 'Mercado Nacional es nuestra marca exclusiva para la distribución directa de abarrotes esenciales directo de productores agrícolas locales. Al eliminar intermediarios innecesarios, garantizamos harinas, legumbres y arroces grado 1 de óptimo rendimiento a precios altamente competitivos para abastecer almacenes tradicionales con productos frescos del campo chileno.'
        ]
    ];

    $stmt_update = $pdo->prepare("UPDATE brands SET slug = ?, history = ? WHERE name = ?");
    foreach ($brand_updates as $name => $data) {
        $stmt_update->execute([$data['slug'], $data['history'], $name]);
    }

    // Get brand IDs to insert default products
    $brand_ids = [];
    $res = $pdo->query("SELECT id, name FROM brands")->fetchAll();
    foreach ($res as $row) {
        $brand_ids[$row['name']] = $row['id'];
    }

    // 5. Insert Default Products if empty
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

    // 6. Seed coverage zones (re-seeded to match the requested 33 communes and localities)
    $pdo->exec("TRUNCATE TABLE coverage");
    $zones = [
        ['Rancagua', "Región de O'Higgins", 1],
        ['Machalí', "Región de O'Higgins", 2],
        ['Graneros', "Región de O'Higgins", 3],
        ['Mostazal', "Región de O'Higgins", 4],
        ['San Fernando', "Región de O'Higgins", 5],
        ['Codegua', "Región de O'Higgins", 6],
        ['Pichilemu', "Región de O'Higgins", 7],
        ['Navidad', "Región de O'Higgins", 8],
        ['Paredones', "Región de O'Higgins", 9],
        ['Nancagua', "Región de O'Higgins", 10],
        ['Marchigüe', "Región de O'Higgins", 11],
        ['Santa Cruz', "Región de O'Higgins", 12],
        ['Litueche', "Región de O'Higgins", 13],
        ['Placilla', "Región de O'Higgins", 14],
        ['La Estrella', "Región de O'Higgins", 15],
        ['Chépica', "Región de O'Higgins", 16],
        ['Coinco', "Región de O'Higgins", 17],
        ['Pumanque', "Región de O'Higgins", 18],
        ['Lolol', "Región de O'Higgins", 19],
        ['Palmilla', "Región de O'Higgins", 20],
        ['Doñihue', "Región de O'Higgins", 21],
        ['Requínoa', "Región de O'Higgins", 22],
        ['Lo Miranda', "Región de O'Higgins", 23],
        ['Coltauco', "Región de O'Higgins", 24],
        ['Las Cabras', "Región de O'Higgins", 25],
        ['Chimbarongo', "Región de O'Higgins", 26],
        ['Rengo', "Región de O'Higgins", 27],
        ['Olivar', "Región de O'Higgins", 28],
        ['Malloa', "Región de O'Higgins", 29],
        ['San Vicente de Tagua Tagua', "Región de O'Higgins", 30],
        ['Peumo', "Región de O'Higgins", 31],
        ['Pichidegua', "Región de O'Higgins", 32],
        ['Peralillo', "Región de O'Higgins", 33]
    ];
    $stmt = $pdo->prepare("INSERT INTO coverage (`name`, `region`, `sort_order`) VALUES (?, ?, ?)");
    foreach ($zones as $z) {
        $stmt->execute($z);
    }

    // 7. Seed offers/banners if empty
    $check_offers = $pdo->query("SELECT COUNT(*) FROM offers")->fetchColumn();
    if ($check_offers == 0) {
        $offers = [
            [
                'Campaña Supermercados de Barrio',
                'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&auto=format&fit=crop&q=60',
                'Abastece tu almacén o minimarket con nuestro portafolio de marcas líderes en abarrotes. Obtén tarifas preferenciales por volumen.',
                '#contacto',
                'Activa',
                'Campañas comerciales',
                1
            ],
            [
                'Despacho Express O\'Higgins',
                'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&auto=format&fit=crop&q=60',
                'Despachos asegurados en menos de 48 horas en Rancagua, Machalí, Graneros y comunas asociadas.',
                '#contacto',
                'Activa',
                'Promociones informativas',
                2
            ]
        ];
        $stmt = $pdo->prepare("INSERT INTO offers (`title`, `image_url`, `description`, `link_url`, `status`, `type`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($offers as $o) {
            $stmt->execute($o);
        }
    }

    // 8. Seed partners if empty
    $check_partners = $pdo->query("SELECT COUNT(*) FROM partners")->fetchColumn();
    if ($check_partners == 0) {
        $partners = [
            [
                'Supermercado El Cóndor',
                'https://images.unsplash.com/photo-1542838132-92c53300491e?w=120&auto=format&fit=crop&q=60',
                'Socio estratégico en distribución minorista en Rancagua.',
                '#',
                1
            ],
            [
                'Panificadora San José',
                'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=120&auto=format&fit=crop&q=60',
                'Cliente destacado de harinas e insumos de panadería.',
                '#',
                2
            ],
            [
                'Minimarket O\'Higgins',
                'https://images.unsplash.com/photo-1542838132-92c53300491e?w=120&auto=format&fit=crop&q=60',
                'Cadena regional de minimarkets abastecidos por La Canasta.',
                '#',
                3
            ]
        ];
        $stmt = $pdo->prepare("INSERT INTO partners (`name`, `logo_url`, `description`, `link_url`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
        foreach ($partners as $p) {
            $stmt->execute($p);
        }
    }

    // 9. Seed sub_brands if empty
    $check_sub_brands = $pdo->query("SELECT COUNT(*) FROM sub_brands")->fetchColumn();
    if ($check_sub_brands == 0) {
        $sub_brands = [
            ['Belmont', 'https://placehold.co/150x50/f3f4f6/0f2c59?text=Belmont', 1],
            ['Iansa Cero', 'https://placehold.co/150x50/f3f4f6/0f2c59?text=Iansa+Cero', 2],
            ['Life', 'https://placehold.co/150x50/f3f4f6/0f2c59?text=Life', 3],
            ['Crucina', 'https://placehold.co/150x50/f3f4f6/0f2c59?text=Crucina', 4],
            ['Traverso Gourmet', 'https://placehold.co/150x50/f3f4f6/0f2c59?text=Traverso+Gourmet', 5],
            ['Sureña', 'https://placehold.co/150x50/f3f4f6/0f2c59?text=Sure%C3%B1a', 6]
        ];
        $stmt = $pdo->prepare("INSERT INTO sub_brands (`name`, `logo_url`, `sort_order`) VALUES (?, ?, ?)");
        foreach ($sub_brands as $sb) {
            $stmt->execute($sb);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Tablas de la base de datos creadas, actualizadas e inicializadas con éxito.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error durante el setup de base de datos: ' . $e->getMessage()
    ]);
}
