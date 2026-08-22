<?php
// api/export.php
// Exports leads database to CSV or Excel compatible format (Filtered support)

require_once 'db.php';
require_once 'auth.php';

// Verification is mandatory
require_admin_auth();

$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

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
    
    if ($format === 'xls') {
        // Excel format (via tab-separated value file with .xls extension)
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=leads_canasta_' . date('Ymd_His') . '.xls');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Print UTF-8 BOM
        echo "\xEF\xBB\xBF";
        
        // Headers
        echo "ID\tNombre\tRUT\tEmpresa\tCargo\tTeléfono\tEmail\tRegión\tComuna\tComentarios\tOrigen del Lead\tEstado\tFecha Registro\n";
        
        foreach ($leads as $row) {
            // Clean tabs/newlines in comments
            $comments = str_replace(["\r", "\n", "\t"], ' ', $row['comments']);
            $rut = isset($row['rut']) ? $row['rut'] : '';
            $comuna = isset($row['comuna']) ? $row['comuna'] : '';
            echo "{$row['id']}\t{$row['name']}\t{$rut}\t{$row['company']}\t{$row['role']}\t{$row['phone']}\t{$row['email']}\t{$row['region']}\t{$comuna}\t{$comments}\t{$row['origin']}\t{$row['status']}\t{$row['created_at']}\n";
        }
    } else {
        // CSV format (Semicolon separated, UTF-8 with BOM for Spanish Excel compatibility)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=leads_canasta_' . date('Ymd_His') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Print UTF-8 BOM
        echo "\xEF\xBB\xBF";
        
        $output = fopen('php://output', 'w');
        
        // Column Headers
        fputcsv($output, ['ID', 'Nombre', 'RUT', 'Empresa', 'Cargo', 'Teléfono', 'Email', 'Región', 'Comuna', 'Comentarios', 'Origen del Lead', 'Estado', 'Fecha Registro'], ';');
        
        foreach ($leads as $row) {
            $rut = isset($row['rut']) ? $row['rut'] : '';
            $comuna = isset($row['comuna']) ? $row['comuna'] : '';
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $rut,
                $row['company'],
                $row['role'],
                $row['phone'],
                $row['email'],
                $row['region'],
                $comuna,
                $row['comments'],
                $row['origin'],
                $row['status'],
                $row['created_at']
            ], ';');
        }
        
        fclose($output);
    }
    exit;
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al exportar datos: ' . $e->getMessage()
    ]);
}
