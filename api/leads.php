<?php
// api/leads.php
// Manages lead capture and retrieval

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // 1. Update status of lead (Admin Action)
    if ($action === 'update_status') {
        require_admin_auth();
        $data = json_decode(file_get_contents('php://input'), true);
        $lead_id = isset($data['id']) ? intval($data['id']) : 0;
        $new_status = isset($data['status']) ? trim($data['status']) : '';
        
        $allowed_statuses = ['Nuevo', 'Contactado', 'En Seguimiento', 'Cliente'];
        if ($lead_id <= 0 || !in_array($new_status, $allowed_statuses)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos para actualizar el estado del lead.'
            ]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $lead_id]);
            echo json_encode([
                'status' => 'success',
                'message' => 'Estado del lead actualizado con éxito.'
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ]);
            exit;
        }
    }
    
    // 2. Capture new lead (Public Action)
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Fallback if not JSON
    if (!$data) {
        $data = $_POST;
    }
    
    $name = isset($data['name']) ? trim($data['name']) : '';
    $rut_raw = isset($data['rut']) ? trim($data['rut']) : '';
    $company = isset($data['company']) ? trim($data['company']) : '';
    $role = isset($data['role']) ? trim($data['role']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $region = isset($data['region']) ? trim($data['region']) : '';
    $comuna = isset($data['comuna']) ? trim($data['comuna']) : '';
    $comments = isset($data['comments']) ? trim($data['comments']) : '';
    $origin = isset($data['origin']) ? trim($data['origin']) : 'Formulario General';
    
    // RUT Validation and Normalization
    function valida_rut($rut) {
        $rut = preg_replace('/[^kK0-9]/i', '', $rut);
        if (strlen($rut) < 8) return false;
        $dv = substr($rut, -1);
        $numero = substr($rut, 0, strlen($rut) - 1);
        $i = 2;
        $suma = 0;
        foreach (array_reverse(str_split($numero)) as $v) {
            if ($i == 8) $i = 2;
            $suma += $v * $i;
            ++$i;
        }
        $dvr = 11 - ($suma % 11);
        if ($dvr == 11) $dvr = 0;
        if ($dvr == 10) $dvr = 'K';
        return strtoupper($dv) == strtoupper($dvr);
    }
    
    function normaliza_rut($rut) {
        $rut = preg_replace('/[^kK0-9]/i', '', $rut);
        if (empty($rut)) return '';
        $dv = substr($rut, -1);
        $numero = substr($rut, 0, strlen($rut) - 1);
        return number_format($numero, 0, "", ".") . '-' . strtoupper($dv);
    }

    $rut = '';
    if (!empty($rut_raw)) {
        if (!valida_rut($rut_raw)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'El RUT ingresado no es válido.'
            ]);
            exit;
        }
        $rut = normaliza_rut($rut_raw);
    }
    
    // Server-side required fields & email format validation
    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($rut)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Nombre, RUT y correo electrónico válido son campos obligatorios.'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO leads (name, rut, company, role, phone, email, region, comuna, comments, origin, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Nuevo')");
        $stmt->execute([$name, $rut, $company, $role, $phone, $email, $region, $comuna, $comments, $origin]);
        
        // --- Send Email Notifications ---
        
        // 1. Email to Admin
        $admin_email = 'contacto@lacanastacomercializadora.cl';
        $admin_subject = "=?UTF-8?B?" . base64_encode("[Nuevo Lead] La Canasta - $origin") . "?=";
        $admin_headers = "MIME-Version: 1.0\r\n";
        $admin_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $admin_headers .= "From: La Canasta Web <no-reply@lacanastacomercializadora.cl>\r\n";
        
        $admin_body = "
        <html>
        <head>
            <title>Nuevo Lead Recibido</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                <h2 style='color: #5d4637; border-bottom: 2px solid #b83b1d; padding-bottom: 10px;'>Nuevo Lead Capturado</h2>
                <p>Se ha recibido una nueva solicitud comercial a través del sitio web:</p>
                <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                    <tr><td style='padding: 8px 0; font-weight: bold; width: 150px;'>Nombre:</td><td>" . htmlspecialchars($name) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>RUT:</td><td>" . htmlspecialchars($rut) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Empresa:</td><td>" . htmlspecialchars($company) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Cargo:</td><td>" . htmlspecialchars($role) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Teléfono:</td><td>" . htmlspecialchars($phone) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Correo:</td><td><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Región:</td><td>" . htmlspecialchars($region) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Comuna:</td><td>" . htmlspecialchars($comuna) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Origen:</td><td><span style='background: #5d4637; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.85em;'>" . htmlspecialchars($origin) . "</span></td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Comentarios:</td><td>" . nl2br(htmlspecialchars($comments)) . "</td></tr>
                </table>
                <p style='margin-top: 25px; font-size: 0.85em; color: #777; border-top: 1px dashed #ddd; padding-top: 10px;'>Este mensaje fue enviado de manera automática desde el servidor de La Canasta Comercializadora.</p>
            </div>
        </body>
        </html>
        ";
        
        @mail($admin_email, $admin_subject, $admin_body, $admin_headers);

        // 2. Confirmation/Welcome Email to the Lead
        $user_subject = "=?UTF-8?B?" . base64_encode("Recibimos tu solicitud - La Canasta") . "?=";
        $user_headers = "MIME-Version: 1.0\r\n";
        $user_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $user_headers .= "From: La Canasta <contacto@lacanastacomercializadora.cl>\r\n";
        
        $user_body = "
        <html>
        <head>
            <title>Recibimos tu solicitud</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                <h2 style='color: #5d4637; border-bottom: 2px solid #b83b1d; padding-bottom: 10px;'>La Canasta</h2>
                <p>Hola <strong>" . htmlspecialchars($name) . "</strong>,</p>
                <p>Gracias por contactarnos. Un ejecutivo de La Canasta Comercializadora y Distribuidora se pondrá en contacto con usted a la brevedad.</p>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 20px;'>
                    <h4 style='margin-top: 0; color: #5d4637;'>Resumen de tu solicitud:</h4>
                    <p style='margin: 5px 0;'><strong>Empresa:</strong> " . htmlspecialchars($company) . "</p>
                    <p style='margin: 5px 0;'><strong>Región/Comuna:</strong> " . htmlspecialchars($region) . "</p>
                </div>
                <p style='margin-top: 25px;'>Saludos cordiales,<br><strong>Equipo Comercial</strong><br>La Canasta Comercializadora y Distribuidora</p>
            </div>
        </body>
        </html>
        ";
        
        @mail($email, $user_subject, $user_body, $user_headers);

        echo json_encode([
            'status' => 'success',
            'message' => 'Gracias por contactarnos. Un ejecutivo de La Canasta Comercializadora y Distribuidora se pondrá en contacto con usted a la brevedad.'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al guardar los datos de contacto: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'GET') {
    // 3. Retrieve leads for admin panel with filters
    require_admin_auth();
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $region = isset($_GET['region']) ? trim($_GET['region']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
    
    try {
        $sql = "SELECT * FROM leads WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR company LIKE ? OR role LIKE ? OR email LIKE ? OR comments LIKE ? OR phone LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
        }
        
        if (!empty($region)) {
            $sql .= " AND region = ?";
            $params[] = $region;
        }

        if (!empty($status)) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        if (!empty($start_date)) {
            $sql .= " AND DATE(created_at) >= ?";
            $params[] = $start_date;
        }
        
        if (!empty($end_date)) {
            $sql .= " AND DATE(created_at) <= ?";
            $params[] = $end_date;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
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
        'message' => 'Método no permitido.'
    ]);
}

