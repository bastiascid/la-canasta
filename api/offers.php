<?php
// api/offers.php
// Offers & Banners CRUD operations

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List offers (public or admin)
    $headers = getallheaders();
    $passcode = isset($headers['X-Admin-Passcode']) ? $headers['X-Admin-Passcode'] : (isset($headers['x-admin-passcode']) ? $headers['x-admin-passcode'] : null);
    
    try {
        if ($passcode === 'admin123') {
            $stmt = $pdo->query("SELECT * FROM offers ORDER BY sort_order ASC, id DESC");
        } else {
            $stmt = $pdo->query("SELECT * FROM offers WHERE status = 'Activa' ORDER BY sort_order ASC, id DESC");
        }
        $offers = $stmt->fetchAll();
        echo json_encode([
            'status' => 'success',
            'data' => $offers
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener las ofertas: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'POST' && (!isset($_GET['action']) || $_GET['action'] !== 'delete')) {
    // Create or Update offer
    require_admin_auth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $id = isset($data['id']) ? intval($data['id']) : null;
    $title = isset($data['title']) ? trim($data['title']) : '';
    $image_url = isset($data['image_url']) ? trim($data['image_url']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $link_url = isset($data['link_url']) ? trim($data['link_url']) : '#';
    $status = isset($data['status']) ? trim($data['status']) : 'Activa';
    $type = isset($data['type']) ? trim($data['type']) : 'Campañas comerciales';
    $sort_order = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
    
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'El título de la oferta es obligatorio.']);
        exit;
    }
    
    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE offers SET title = ?, image_url = ?, description = ?, link_url = ?, status = ?, type = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$title, $image_url, $description, $link_url, $status, $type, $sort_order, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Oferta actualizada con éxito.']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO offers (title, image_url, description, link_url, status, type, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $image_url, $description, $link_url, $status, $type, $sort_order]);
            echo json_encode(['status' => 'success', 'message' => 'Oferta creada con éxito.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar la oferta: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE' || ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete')) {
    // Delete offer
    require_admin_auth();
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : null;
    }
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID de oferta no especificado.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM offers WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Oferta eliminada con éxito.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la oferta: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
