<?php
header("Content-Type: application/json");

// ===== GET PARAM =====
$id = $_GET['id'] ?? '';

if (!$id) {
    http_response_code(400);
    echo json_encode(["error" => "ID missing"], JSON_UNESCAPED_SLASHES);
    exit;
}

// ===== LOAD KEYS JSON =====
$dataFile = __DIR__ . "/app/og.json";

if (!file_exists($dataFile)) {
    http_response_code(500);
    echo json_encode(["error" => "No Data"], JSON_UNESCAPED_SLASHES);
    exit;
}

$data = json_decode(file_get_contents($dataFile), true);

if (!is_array($data)) {
    http_response_code(500);
    echo json_encode(["error" => "Invalid JSON"], JSON_UNESCAPED_SLASHES);
    exit;
}

// ===== FIND CHANNEL =====
$found = null;

foreach ($data as $item) {
    if ((string)$item['channel_id'] === (string)$id) {
        $found = $item;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(["error" => "Not Found"], JSON_UNESCAPED_SLASHES);
    exit;
}

// ===== HEX TO BASE64 =====
function hexToBase64($hex) {
    // validate hex
    if (!ctype_xdigit($hex)) {
        return null;
    }

    $bin = hex2bin($hex);
    if ($bin === false) {
        return null;
    }

    return rtrim(base64_encode($bin), '=');
}

$k   = hexToBase64($found['key']);
$kid = hexToBase64($found['keyId']);

if (!$k || !$kid) {
    http_response_code(500);
    echo json_encode(["error" => "Invalid key format"], JSON_UNESCAPED_SLASHES);
    exit;
}

// ===== RESPONSE =====
echo json_encode([
    "keys" => [[
        "k"   => $k,
        "kid" => $kid,
        "kty" => "oct"
    ]],
    "type" => "temporary"
], JSON_UNESCAPED_SLASHES);