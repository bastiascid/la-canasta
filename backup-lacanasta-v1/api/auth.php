<?php
// api/auth.php
// Middleware basic check for admin passcode

function require_admin_auth() {
    $headers = getallheaders();
    $passcode = null;
    
    // Look for passcode header or GET parameter
    if (isset($headers['X-Admin-Passcode'])) {
        $passcode = $headers['X-Admin-Passcode'];
    } elseif (isset($headers['x-admin-passcode'])) {
        $passcode = $headers['x-admin-passcode'];
    } elseif (isset($_GET['passcode'])) {
        $passcode = $_GET['passcode'];
    }
    
    // Match against default passcode
    if ($passcode !== 'admin123') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'No autorizado. Codigo de acceso incorrecto.'
        ]);
        exit;
    }
}
