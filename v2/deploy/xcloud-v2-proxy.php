<?php
$target = 'http://127.0.0.1:3001' . ($_SERVER['REQUEST_URI'] ?? '/v2');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$upstreamMethod = $method === 'HEAD' ? 'GET' : $method;
$headers = [];

foreach (getallheaders() as $name => $value) {
    $lower = strtolower($name);
    if (in_array($lower, ['host', 'content-length', 'connection'], true)) {
        continue;
    }
    $headers[] = $name . ': ' . $value;
}

$body = file_get_contents('php://input');
$ch = curl_init($target);
curl_setopt_array($ch, [
    CURLOPT_CUSTOMREQUEST => $upstreamMethod,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_POSTFIELDS => $body ?: null,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_FOLLOWLOCATION => false,
]);

$response = curl_exec($ch);
if ($response === false) {
    http_response_code(502);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'HarnessBid v2 preview is temporarily unavailable.';
    exit;
}

$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: 200;
$rawHeaders = substr($response, 0, $headerSize);
$content = substr($response, $headerSize);
http_response_code($status);

foreach (explode("\r\n", $rawHeaders) as $header) {
    if ($header === '' || stripos($header, 'HTTP/') === 0) {
        continue;
    }
    if (preg_match('/^(transfer-encoding|connection|content-length):/i', $header)) {
        continue;
    }
    header($header, false);
}

if ($method !== 'HEAD') {
    echo $content;
}
