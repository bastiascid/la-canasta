<?php
// api/brands.php
// Brand CRUD operations (Extended with slug & history)

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // Try transliterating or replacing special characters
    $text = str_replace(['ñ', 'Ñ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['n', 'n', 'a', 'e', 'i', 'o', 'u', 'a', 'e', 'i', 'o', 'u'], $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'brand';
    }
    return $text;
}

if ($method === 'GET') {
    // List brands (public or admin)
    // If admin header is present and valid, return all brands, otherwise only active ones
    $headers = getallheaders();
    $passcode = isset($headers['X-Admin-Passcode']) ? $headers['X-Admin-Passcode'] : (isset($headers['x-admin-passcode']) ? $headers['x-admin-passcode'] : null);
    
    // Check if slug is requested via GET (e.g. for brand landing page details)
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;
    
    try {
        if ($slug) {
            $stmt = $pdo->prepare("SELECT * FROM brands WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $brand = $stmt->fetch();
            if ($brand) {
                echo json_encode([
                    'status' => 'success',
                    'data' => $brand
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Marca no encontrada.'
                ]);
            }
        } else {
            if ($passcode === 'admin123') {
                $stmt = $pdo->query("SELECT * FROM brands ORDER BY sort_order ASC, name ASC");
            } else {
                $stmt = $pdo->query("SELECT * FROM brands WHERE status = 'Activa' ORDER BY sort_order ASC, name ASC");
            }
            $brands = $stmt->fetchAll();
            echo json_encode([
                'status' => 'success',
                'data' => $brands
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener las marcas: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'POST' && (!isset($_GET['action']) || $_GET['action'] !== 'delete')) {
    // Create or Update brand
    require_admin_auth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $id = isset($data['id']) ? intval($data['id']) : null;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $logo_url = isset($data['logo_url']) ? trim($data['logo_url']) : '';
    $image_url = isset($data['image_url']) ? trim($data['image_url']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $status = isset($data['status']) ? trim($data['status']) : 'Activa';
    $sort_order = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
    $official_url = isset($data['official_url']) ? trim($data['official_url']) : '';
    $history = isset($data['history']) ? trim($data['history']) : '';
    $slug = isset($data['slug']) ? trim($data['slug']) : '';
    
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'El nombre de la marca es obligatorio.']);
        exit;
    }
    
    // Process slug
    if (empty($slug)) {
        $slug = slugify($name);
    } else {
        $slug = slugify($slug);
    }
    
    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, logo_url = ?, image_url = ?, description = ?, status = ?, sort_order = ?, official_url = ?, history = ?, slug = ? WHERE id = ?");
            $stmt->execute([$name, $logo_url, $image_url, $description, $status, $sort_order, $official_url, $history, $slug, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Marca actualizada con éxito.']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO brands (name, logo_url, image_url, description, status, sort_order, official_url, history, slug) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $logo_url, $image_url, $description, $status, $sort_order, $official_url, $history, $slug]);
            echo json_encode(['status' => 'success', 'message' => 'Marca creada con éxito.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar la marca: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE' || ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete')) {
    // Delete brand
    require_admin_auth();
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : null;
    }
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID de marca no especificado.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Marca eliminada con éxito.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la marca: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
