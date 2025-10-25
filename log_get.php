<?php
// Accept GET requests with either ?line=... (raw) or ?b=... (base64-encoded)
$line = '';

if (isset($_GET['b']) && $_GET['b'] !== '') {
    // base64 param (preferred)
    // sanitize: strip any characters that are not base64-safe before decoding
    $b = preg_replace('/[^A-Za-z0-9+\/=]/', '', $_GET['b']);
    $decoded = base64_decode($b, true);
    if ($decoded !== false) {
        $line = $decoded;
    } else {
        http_response_code(400);
        echo "Invalid base64.";
        exit;
    }
} elseif (isset($_GET['line'])) {
    $line = $_GET['line'];
} else {
    // nothing to log
    http_response_code(204);
    exit;
}

$line = trim($line);
if ($line === '') {
    http_response_code(400);
    echo "Empty line.";
    exit;
}

$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

$ip = $_SERVER['REMOTE_ADDR'];
$ip = preg_replace('/[^0-9a-fA-F\.:]/', '_', $ip);

$date = date('Y-m-d');
$time = date('H:i:s');
$filePath = "$logDir/{$date}_{$ip}.txt";

// Append the single line with a timestamp
file_put_contents($filePath, "[$time] $line\n", FILE_APPEND | LOCK_EX);

http_response_code(200);
echo "OK";
?>
