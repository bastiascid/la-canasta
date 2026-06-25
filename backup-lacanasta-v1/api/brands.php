<?php
// api/brands.php
// Brand CRUD operations

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List brands (public or admin)
    // If admin header is present and valid, return all brands, otherwise only active ones
    $headers = getallheaders();
    $passcode = isset($headers['X-Admin-Passcode']) ? $headers['X-Admin-Passcode'] : (isset($headers['x-admin-passcode']) ? $headers['x-admin-passcode'] : null);
    
    try {
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
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener las marcas: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'POST') {
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
    
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'El nombre de la marca es obligatorio.']);
        exit;
    }
    
    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, logo_url = ?, image_url = ?, description = ?, status = ?, sort_order = ?, official_url = ? WHERE id = ?");
            $stmt->execute([$name, $logo_url, $image_url, $description, $status, $sort_order, $official_url, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Marca actualizada con exito.']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO brands (name, logo_url, image_url, description, status, sort_order, official_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $logo_url, $image_url, $description, $status, $sort_order, $official_url]);
            echo json_encode(['status' => 'success', 'message' => 'Marca creada con exito.']);
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
        echo json_encode(['status' => 'success', 'message' => 'Marca eliminada con exito.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la marca: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metodo no permitido.']);
}
