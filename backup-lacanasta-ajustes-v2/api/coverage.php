<?php
// api/coverage.php
// Coverage Zones CRUD operations

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List coverage zones
    try {
        $stmt = $pdo->query("SELECT * FROM coverage ORDER BY sort_order ASC, name ASC");
        $zones = $stmt->fetchAll();
        echo json_encode([
            'status' => 'success',
            'data' => $zones
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener las zonas de cobertura: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'POST' && (!isset($_GET['action']) || $_GET['action'] !== 'delete')) {
    // Create or Update zone
    require_admin_auth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $id = isset($data['id']) ? intval($data['id']) : null;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $region = isset($data['region']) ? trim($data['region']) : "Región de O'Higgins";
    $sort_order = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
    
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'El nombre de la zona es obligatorio.']);
        exit;
    }
    
    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE coverage SET name = ?, region = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $region, $sort_order, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Zona de cobertura actualizada con éxito.']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO coverage (name, region, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$name, $region, $sort_order]);
            echo json_encode(['status' => 'success', 'message' => 'Zona de cobertura creada con éxito.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar la zona de cobertura: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE' || ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete')) {
    // Delete zone
    require_admin_auth();
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : null;
    }
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID de zona de cobertura no especificado.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM coverage WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Zona de cobertura eliminada con éxito.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar la zona de cobertura: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
