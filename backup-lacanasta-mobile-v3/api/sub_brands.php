<?php
// api/sub_brands.php
// Sub-Brands (Individual Brands) CRUD operations

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List sub-brands (public or admin)
    $headers = getallheaders();
    $passcode = isset($headers['X-Admin-Passcode']) ? $headers['X-Admin-Passcode'] : (isset($headers['x-admin-passcode']) ? $headers['x-admin-passcode'] : null);
    
    try {
        if ($passcode === 'admin123') {
            $stmt = $pdo->query("SELECT * FROM sub_brands ORDER BY sort_order ASC, id DESC");
        } else {
            $stmt = $pdo->query("SELECT * FROM sub_brands WHERE status = 'Activo' ORDER BY sort_order ASC, id DESC");
        }
        $sub_brands = $stmt->fetchAll();
        echo json_encode([
            'status' => 'success',
            'data' => $sub_brands
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener las marcas individuales: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'POST' && (!isset($_GET['action']) || $_GET['action'] !== 'delete')) {
    // Create or Update sub-brand
    require_admin_auth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $id = isset($data['id']) ? intval($data['id']) : null;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $logo_url = isset($data['logo_url']) ? trim($data['logo_url']) : '';
    $status = isset($data['status']) ? trim($data['status']) : 'Activo';
    $sort_order = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
    
    if (empty($name) || empty($logo_url)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'El nombre y la URL del logo son obligatorios.']);
        exit;
    }
    
    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE sub_brands SET name = ?, logo_url = ?, status = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $logo_url, $status, $sort_order, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Marca individual actualizada con éxito.']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO sub_brands (name, logo_url, status, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $logo_url, $status, $sort_order]);
            echo json_encode(['status' => 'success', 'message' => 'Marca individual creada con éxito.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar la marca individual: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE' || ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete')) {
    // Delete sub-brand
    require_admin_auth();
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : null;
    }
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID de marca individual no especificado.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM sub_brands WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Marca individual eliminada con éxito.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la marca individual: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
