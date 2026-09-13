<?php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
    exit;
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No se ha recibido ninguna imagen.']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Error al subir la imagen.']);
    exit;
}

if ($file['size'] > 10 * 1024 * 1024) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'La imagen supera el límite de 10 MB.']);
    exit;
}

$imageInfo = getimagesize($file['tmp_name']);

if ($imageInfo === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El archivo no es una imagen válida.']);
    exit;
}

$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif'
];

$mimeType = $imageInfo['mime'];

if (!isset($allowedTypes[$mimeType])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Formato de imagen no permitido.']);
    exit;
}

$uploadDir = dirname(__DIR__) . '/imagenes/blog/';

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo crear la carpeta de imágenes.']);
        exit;
    }
}

$extension = $allowedTypes[$mimeType];
$fileName = 'blog_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
$destination = $uploadDir . $fileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'No se pudo guardar la imagen.']);
    exit;
}

$relativePath = 'imagenes/blog/' . $fileName;

echo json_encode([
    'success' => true,
    'path' => $relativePath,
    'filename' => $fileName
]);

?>
