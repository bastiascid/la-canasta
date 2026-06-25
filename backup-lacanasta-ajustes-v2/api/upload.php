<?php
// api/upload.php
// Handles file uploads from the admin panel

require_once 'db.php';
require_once 'auth.php';

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
    exit;
}

// Check admin auth
require_admin_auth();

// Verify file was uploaded
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No se subió ningún archivo.']);
    exit;
}

$file = $_FILES['file'];

// Check for errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo (Código ' . $file['error'] . ')']);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'El archivo supera el tamaño máximo permitido (5MB).']);
    exit;
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime_type, $allowed_types)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tipo de archivo no permitido. Solo se aceptan imágenes (JPG, PNG, GIF, WEBP, SVG).']);
    exit;
}

// Get extension
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
if (empty($ext)) {
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg'
    ];
    $ext = isset($map[$mime_type]) ? $map[$mime_type] : 'png';
}

// Generate unique filename
$filename = 'up_' . uniqid() . '.' . strtolower($ext);

// Ensure upload directory exists
$upload_dir = '../assets/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$target_path = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $target_path)) {
    $relative_url = 'assets/' . $filename;
    echo json_encode([
        'status' => 'success',
        'message' => 'Archivo subido con éxito.',
        'url' => $relative_url
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'No se pudo mover el archivo subido al directorio de destino.']);
}
?>
