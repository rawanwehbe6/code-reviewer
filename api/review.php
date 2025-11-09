<?php

declare(strict_types=1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

$rawBody = file_get_contents('php://input');
if ($rawBody === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Unable to read request body']);
    exit;
}

$data = json_decode($rawBody, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

if (!isset($data['code']) || !is_string($data['code']) || trim($data['code']) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid \"code\" field']);
    exit;
}

$code     = $data['code'];
$filename = isset($data['filename']) && is_string($data['filename']) ? $data['filename'] : null;
$language = isset($data['language']) && is_string($data['language']) ? $data['language'] : null;

http_response_code(200);
echo json_encode([
    'code'     => $code,
    'filename' => $filename,
    'language' => $language,
]);
