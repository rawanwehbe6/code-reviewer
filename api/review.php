<?php

declare(strict_types=1);

$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;

    if (class_exists(\Dotenv\Dotenv::class)) {
        \Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
    }
}

header('Content-Type: application/json');

$configPath  = __DIR__ . '/../src/Config.php';
$servicePath = __DIR__ . '/../src/ReviewService.php';

if (!file_exists($configPath) || !file_exists($servicePath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error: missing core files.']);
    exit;
}

require_once $configPath;
require_once $servicePath;

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

try {
    $issues = ReviewService::review($code, $filename, $language);

    if (!is_array($issues)) {
        $issues = [];
    }

    http_response_code(200);
    echo json_encode($issues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    $details = Config::STRICT_SCHEMA_MODE
        ? 'Schema validation or AI call failed'
        : $e->getMessage();

    http_response_code(500);
    echo json_encode([
        'error'   => 'Internal error',
        'details' => $details,
    ]);
}
