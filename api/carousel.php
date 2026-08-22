<?php
// api/carousel.php
// Manages the background carousel for the Hero section

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- PUBLIC ENDPOINTS ---

if ($method === 'GET' && $action === '') {
    try {
        $stmt = $pdo->query("SELECT * FROM carousel_portada WHERE activo = 1 ORDER BY orden ASC, created_at DESC");
        $items = $stmt->fetchAll();
        echo json_encode([
            'status' => 'success',
            'data' => $items
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener el carrusel: ' . $e->getMessage()
        ]);
    }
    exit;
}

// --- ADMIN ENDPOINTS ---

// Require authentication for all actions below
require_admin_auth();

if ($method === 'GET' && $action === 'list_all') {
    try {
        $stmt = $pdo->query("SELECT * FROM carousel_portada ORDER BY orden ASC, created_at DESC");
        $items = $stmt->fetchAll();
        echo json_encode([
            'status' => 'success',
            'data' => $items
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener todos los items: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($method === 'POST') {
    // Determine if it's an update or create
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Validate inputs
    $producto_id = isset($_POST['producto_id']) && $_POST['producto_id'] !== '' ? intval($_POST['producto_id']) : null;
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $marca = isset($_POST['marca']) ? trim($_POST['marca']) : '';
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
    $orden = isset($_POST['orden']) ? intval($_POST['orden']) : 0;
    $activo = isset($_POST['activo']) ? intval($_POST['activo']) : 1;
    
    $image_url = '';
    
    // File upload validation
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        
        $file_mime = mime_content_type($file['tmp_name']);
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file_size = $file['size'];
        
        if (!in_array($file_mime, $allowed_mimes) || !in_array($file_ext, $allowed_exts)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no válido. Solo JPG, PNG, WEBP.']);
            exit;
        }
        
        if ($file_size > 5 * 1024 * 1024) { // 5MB max
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'La imagen excede el límite de 5MB.']);
            exit;
        }
        
        // Secure filename
        $safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $new_filename = 'carousel_' . time() . '_' . $safe_name . '.' . $file_ext;
        $upload_dir = __DIR__ . '/../assets/carousel/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $target_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            $image_url = 'assets/carousel/' . $new_filename;
        } else {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar la imagen.']);
            exit;
        }
    }
    
    try {
        if ($id > 0) {
            // Update
            if ($image_url) {
                $stmt = $pdo->prepare("UPDATE carousel_portada SET producto_id=?, imagen=?, titulo=?, marca=?, descripcion=?, orden=?, activo=? WHERE id=?");
                $stmt->execute([$producto_id, $image_url, $titulo, $marca, $descripcion, $orden, $activo, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE carousel_portada SET producto_id=?, titulo=?, marca=?, descripcion=?, orden=?, activo=? WHERE id=?");
                $stmt->execute([$producto_id, $titulo, $marca, $descripcion, $orden, $activo, $id]);
            }
            echo json_encode(['status' => 'success', 'message' => 'Item actualizado correctamente.']);
        } else {
            // Create
            if (!$image_url) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'La imagen es obligatoria para nuevos items.']);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO carousel_portada (producto_id, imagen, titulo, marca, descripcion, orden, activo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$producto_id, $image_url, $titulo, $marca, $descripcion, $orden, $activo]);
            echo json_encode(['status' => 'success', 'message' => 'Item creado correctamente.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error BD: ' . $e->getMessage()]);
    }
    exit;
}

if ($method === 'PUT' && $action === 'toggle_status') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = isset($data['id']) ? intval($data['id']) : 0;
    $activo = isset($data['activo']) ? intval($data['activo']) : 1;
    
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE carousel_portada SET activo = ? WHERE id = ?");
            $stmt->execute([$activo, $id]);
            echo json_encode(['status' => 'success', 'message' => 'Estado actualizado.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM carousel_portada WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['status' => 'success', 'message' => 'Item eliminado.']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
