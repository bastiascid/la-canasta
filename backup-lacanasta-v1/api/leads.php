<?php
// api/leads.php
// Manages lead capture and retrieval

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // 1. Capture new lead
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Fallback if not JSON
    if (!$data) {
        $data = $_POST;
    }
    
    $name = isset($data['name']) ? trim($data['name']) : '';
    $company = isset($data['company']) ? trim($data['company']) : '';
    $role = isset($data['role']) ? trim($data['role']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $region = isset($data['region']) ? trim($data['region']) : '';
    $comments = isset($data['comments']) ? trim($data['comments']) : '';
    
    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Nombre y correo electronico son campos obligatorios.'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO leads (name, company, role, phone, email, region, comments) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $company, $role, $phone, $email, $region, $comments]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Tus datos han sido registrados con exito. Pronto nos comunicaremos contigo.'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al guardar los datos de contacto: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'GET') {
    // 2. Retrieve leads for admin panel
    require_admin_auth();
    
    try {
        $stmt = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC");
        $leads = $stmt->fetchAll();
        
        echo json_encode([
            'status' => 'success',
            'data' => $leads
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener los leads: ' . $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Metodo no permitido.'
    ]);
}
