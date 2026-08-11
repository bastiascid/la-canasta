<?php
// sitemap.php
// Generates a dynamic XML sitemap for La Canasta Comercializadora

// Set the correct header so browsers and search engines read it as XML
header("Content-Type: application/xml; charset=utf-8");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$domain = 'https://www.lacanastacomercializadora.cl';

// 1. Static Pages
$static_pages = [
    '/' => ['priority' => '1.0', 'changefreq' => 'daily'],
    '/sobre-nosotros.php' => ['priority' => '0.8', 'changefreq' => 'monthly'],
    '/hazte-cliente.php' => ['priority' => '0.8', 'changefreq' => 'monthly'],
    '/reclamos.php' => ['priority' => '0.6', 'changefreq' => 'monthly'],
    '/privacidad.html' => ['priority' => '0.5', 'changefreq' => 'yearly'],
    '/terminos.html' => ['priority' => '0.5', 'changefreq' => 'yearly'],
    '/flyer.html' => ['priority' => '0.7', 'changefreq' => 'monthly']
];

foreach ($static_pages as $path => $meta) {
    // We use a static date for static pages or the file modification time if we wanted to be more precise.
    // For simplicity and safety, we'll use a static date or the current date.
    $lastmod = date('Y-m-d', filemtime(__DIR__ . ($path === '/' ? '/index.html' : $path)));
    
    echo "  <url>\n";
    echo "      <loc>" . htmlspecialchars($domain . $path, ENT_QUOTES, 'UTF-8') . "</loc>\n";
    echo "      <lastmod>" . $lastmod . "</lastmod>\n";
    echo "      <changefreq>" . $meta['changefreq'] . "</changefreq>\n";
    echo "      <priority>" . $meta['priority'] . "</priority>\n";
    echo "  </url>\n";
}

// 2. Dynamic Brand Pages
try {
    require_once 'api/db.php';
    
    $stmt = $pdo->prepare("SELECT slug FROM brands WHERE status = 'Activa'");
    $stmt->execute();
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($brands as $brand) {
        $slug = htmlspecialchars($brand['slug'], ENT_QUOTES, 'UTF-8');
        echo "  <url>\n";
        echo "      <loc>" . $domain . "/marcas/" . $slug . "</loc>\n";
        echo "      <changefreq>weekly</changefreq>\n";
        echo "      <priority>0.9</priority>\n";
        echo "  </url>\n";
    }
} catch (Exception $e) {
    // If DB fails, we simply don't output the dynamic urls to prevent XML corruption
    // Error logging could be added here
}

echo '</urlset>';
?>
