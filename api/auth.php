<?php
// api/auth.php
// Middleware basic check for admin passcode

// Polyfill for getallheaders() if it does not exist in CGI/FastCGI environments
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $header_name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$header_name] = $value;
            }
        }
        return $headers;
    }
}

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
