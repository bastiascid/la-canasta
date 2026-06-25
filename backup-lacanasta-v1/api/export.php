<?php
// api/export.php
// Exports leads database to CSV or Excel compatible format

require_once 'db.php';
require_once 'auth.php';

// Verification is mandatory
require_admin_auth();

$format = isset($_GET['format']) ? $_GET['format'] : 'csv';

try {
    $stmt = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC");
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
        echo "ID\tNombre\tEmpresa\tCargo\tTelefono\tEmail\tRegion\tComentarios\tFecha Registro\n";
        
        foreach ($leads as $row) {
            // Clean tabs/newlines in comments
            $comments = str_replace(["\r", "\n", "\t"], ' ', $row['comments']);
            echo "{$row['id']}\t{$row['name']}\t{$row['company']}\t{$row['role']}\t{$row['phone']}\t{$row['email']}\t{$row['region']}\t{$comments}\t{$row['created_at']}\n";
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
        fputcsv($output, ['ID', 'Nombre', 'Empresa', 'Cargo', 'Telefono', 'Email', 'Region', 'Comentarios', 'Fecha Registro'], ';');
        
        foreach ($leads as $row) {
            fputcsv($output, [
                $row['id'],
                $row['name'],
                $row['company'],
                $row['role'],
                $row['phone'],
                $row['email'],
                $row['region'],
                $row['comments'],
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
