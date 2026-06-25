<?php
// api/claims.php
// Manages claim submissions, retrieval and export

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // 1. Update status of claim (Admin Action)
    if ($action === 'update_status') {
        require_admin_auth();
        $data = json_decode(file_get_contents('php://input'), true);
        $claim_id = isset($data['id']) ? intval($data['id']) : 0;
        $new_status = isset($data['status']) ? trim($data['status']) : '';
        
        $allowed_statuses = ['Nuevo', 'En Revisión', 'Resuelto', 'Rechazado'];
        if ($claim_id <= 0 || !in_array($new_status, $allowed_statuses)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos para actualizar el estado del reclamo.'
            ]);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("UPDATE claims SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $claim_id]);
            echo json_encode([
                'status' => 'success',
                'message' => 'Estado del reclamo actualizado con éxito.'
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
    
    // 2. Submit new claim (Public Action)
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) {
        $data = $_POST;
    }
    
    $name = isset($data['name']) ? trim($data['name']) : '';
    $company = isset($data['company']) ? trim($data['company']) : '';
    $rut = isset($data['rut']) ? trim($data['rut']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $email = isset($data['email']) ? trim($data['email']) : '';
    $claim_type = isset($data['claim_type']) ? trim($data['claim_type']) : 'General';
    $invoice_number = isset($data['invoice_number']) ? trim($data['invoice_number']) : '';
    $comments = isset($data['comments']) ? trim($data['comments']) : '';
    
    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($comments)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Nombre, correo electrónico válido y detalles del reclamo son campos obligatorios.'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO claims (name, company, rut, phone, email, claim_type, invoice_number, comments, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Nuevo')");
        $stmt->execute([$name, $company, $rut, $phone, $email, $claim_type, $invoice_number, $comments]);
        $claim_id = $pdo->lastInsertId();
        
        // --- Send Email Notifications ---
        $admin_email = 'contacto@lacanastacomercializadora.cl';
        
        // Email to Admin
        $admin_subject = "=?UTF-8?B?" . base64_encode("[Nuevo Reclamo #$claim_id] $claim_type") . "?=";
        $admin_headers = "MIME-Version: 1.0\r\n";
        $admin_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $admin_headers .= "From: La Canasta Web <no-reply@lacanastacomercializadora.cl>\r\n";
        
        $admin_body = "
        <html>
        <head>
            <title>Nuevo Reclamo Recibido</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                <h2 style='color: #5d4637; border-bottom: 2px solid #b83b1d; padding-bottom: 10px;'>Nuevo Reclamo Capturado (#$claim_id)</h2>
                <p>Se ha recibido un nuevo reclamo a través del sitio web:</p>
                <table style='width: 100%; border-collapse: collapse; margin-top: 15px;'>
                    <tr><td style='padding: 8px 0; font-weight: bold; width: 150px;'>Nombre:</td><td>" . htmlspecialchars($name) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Empresa:</td><td>" . htmlspecialchars($company) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>RUT:</td><td>" . htmlspecialchars($rut) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Teléfono:</td><td>" . htmlspecialchars($phone) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Correo:</td><td><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Tipo Reclamo:</td><td>" . htmlspecialchars($claim_type) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>N° Factura/Guía:</td><td>" . htmlspecialchars($invoice_number) . "</td></tr>
                    <tr><td style='padding: 8px 0; font-weight: bold;'>Detalles:</td><td>" . nl2br(htmlspecialchars($comments)) . "</td></tr>
                </table>
                <p style='margin-top: 25px; font-size: 0.85em; color: #777; border-top: 1px dashed #ddd; padding-top: 10px;'>Mensaje automático enviado desde Comercializadora La Canasta.</p>
            </div>
        </body>
        </html>
        ";
        
        @mail($admin_email, $admin_subject, $admin_body, $admin_headers);
        
        // Confirmation Email to Client
        $user_subject = "=?UTF-8?B?" . base64_encode("Recibimos tu reclamo #$claim_id - La Canasta") . "?=";
        $user_headers = "MIME-Version: 1.0\r\n";
        $user_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $user_headers .= "From: La Canasta <contacto@lacanastacomercializadora.cl>\r\n";
        
        $user_body = "
        <html>
        <head>
            <title>Recibimos tu Reclamo</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                <h2 style='color: #5d4637; border-bottom: 2px solid #b83b1d; padding-bottom: 10px;'>Comercializadora y Distribuidora La Canasta</h2>
                <p>Hola <strong>" . htmlspecialchars($name) . "</strong>,</p>
                <p>Hemos recibido correctamente tu requerimiento o reclamo, registrado con el número de seguimiento <strong>#$claim_id</strong>.</p>
                <p>Nuestro equipo de postventa y operaciones lo revisará detalladamente. Nos pondremos en contacto contigo a la brevedad dentro de las próximas 24 a 48 horas hábiles.</p>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 4px; margin-top: 20px;'>
                    <h4 style='margin-top: 0; color: #5d4637;'>Resumen del Reclamo:</h4>
                    <p style='margin: 5px 0;'><strong>Número de Seguimiento:</strong> #$claim_id</p>
                    <p style='margin: 5px 0;'><strong>Tipo:</strong> " . htmlspecialchars($claim_type) . "</p>
                    <p style='margin: 5px 0;'><strong>N° Factura/Guía:</strong> " . htmlspecialchars($invoice_number) . "</p>
                </div>
                <p style='margin-top: 25px;'>Atentamente,<br><strong>Servicio de Postventa</strong><br>Comercializadora La Canasta</p>
            </div>
        </body>
        </html>
        ";
        
        @mail($email, $user_subject, $user_body, $user_headers);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Reclamo ingresado con éxito. Número de caso: #' . $claim_id,
            'case_id' => $claim_id
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al ingresar el reclamo: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'GET') {
    // Admin list or export
    require_admin_auth();
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
    
    try {
        $sql = "SELECT * FROM claims WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR company LIKE ? OR rut LIKE ? OR email LIKE ? OR comments LIKE ? OR invoice_number LIKE ? OR phone LIKE ?)";
            $search_param = "%$search%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
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
        $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($action === 'export') {
            $format = isset($_GET['format']) ? $_GET['format'] : 'xls';
            
            if ($format === 'xls') {
                header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                header('Content-Disposition: attachment; filename=reclamos_canasta_' . date('Ymd_His') . '.xls');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                // BOM UTF-8
                echo "\xEF\xBB\xBF";
                
                echo "ID\tNombre\tEmpresa\tRUT\tTeléfono\tEmail\tTipo Reclamo\tN° Factura/Guía\tDetalles\tEstado\tFecha Registro\n";
                foreach ($claims as $row) {
                    $comments = str_replace(["\r", "\n", "\t"], ' ', $row['comments']);
                    echo "{$row['id']}\t{$row['name']}\t{$row['company']}\t{$row['rut']}\t{$row['phone']}\t{$row['email']}\t{$row['claim_type']}\t{$row['invoice_number']}\t{$comments}\t{$row['status']}\t{$row['created_at']}\n";
                }
            } else {
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=reclamos_canasta_' . date('Ymd_His') . '.csv');
                header('Pragma: no-cache');
                header('Expires: 0');
                
                echo "\xEF\xBB\xBF";
                $output = fopen('php://output', 'w');
                fputcsv($output, ['ID', 'Nombre', 'Empresa', 'RUT', 'Teléfono', 'Email', 'Tipo Reclamo', 'N° Factura/Guía', 'Detalles', 'Estado', 'Fecha Registro'], ';');
                foreach ($claims as $row) {
                    fputcsv($output, [
                        $row['id'],
                        $row['name'],
                        $row['company'],
                        $row['rut'],
                        $row['phone'],
                        $row['email'],
                        $row['claim_type'],
                        $row['invoice_number'],
                        $row['comments'],
                        $row['status'],
                        $row['created_at']
                    ], ';');
                }
                fclose($output);
            }
            exit;
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => $claims
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al obtener reclamos: ' . $e->getMessage()
        ]);
    }
}
?>
