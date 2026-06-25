<?php
// api/products.php
// Product CRUD operations

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List products
    $brand_id = isset($_GET['brand_id']) ? intval($_GET['brand_id']) : null;
    $featured = isset($_GET['featured']) ? intval($_GET['featured']) : null;
    
    try {
        $query = "SELECT p.*, b.name as brand_name FROM products p JOIN brands b ON p.brand_id = b.id";
        $conditions = [];
        $params = [];
        
        if ($brand_id) {
            $conditions[] = "p.brand_id = ?";
            $params[] = $brand_id;
        }
        if ($featured !== null) {
            $conditions[] = "p.featured = ?";
            $params[] = $featured;
        }
        
        // Exclude products of inactive brands for public view
        $headers = getallheaders();
        $passcode = isset($headers['X-Admin-Passcode']) ? $headers['X-Admin-Passcode'] : (isset($headers['x-admin-passcode']) ? $headers['x-admin-passcode'] : null);
        if ($passcode !== 'admin123') {
            $conditions[] = "b.status = 'Activa'";
        }
        
        if (count($conditions) > 0) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $query .= " ORDER BY p.sort_order ASC, p.name ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success',
            'data' => $products
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener los productos: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'POST') {
    // Create or Update product
    require_admin_auth();
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $id = isset($data['id']) ? intval($data['id']) : null;
    $name = isset($data['name']) ? trim($data['name']) : '';
    $brand_id = isset($data['brand_id']) ? intval($data['brand_id']) : null;
    $image_url = isset($data['image_url']) ? trim($data['image_url']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $category = isset($data['category']) ? trim($data['category']) : '';
    $featured = isset($data['featured']) ? intval($data['featured']) : 0;
    $sort_order = isset($data['sort_order']) ? intval($data['sort_order']) : 0;
    
    if (empty($name) || !$brand_id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'El nombre y la marca asociada son obligatorios.']);
        exit;
    }
    
    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE products SET name = ?, brand_id = ?, image_url = ?, description = ?, category = ?, featured = ?, sort_order = ? WHERE id = ?");
            $stmt->execute([$name, $brand_id, $image_url, $description, $category, $featured, $sort_order, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Producto actualizado con exito.']);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO products (name, brand_id, image_url, description, category, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $brand_id, $image_url, $description, $category, $featured, $sort_order]);
            echo json_encode(['status' => 'success', 'message' => 'Producto creado con exito.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar el producto: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE' || ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'delete')) {
    // Delete product
    require_admin_auth();
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if (!$id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = isset($data['id']) ? intval($data['id']) : null;
    }
    
    if (!$id) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'ID de producto no especificado.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Producto eliminado con exito.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el producto: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Metodo no permitido.']);
}
