<?php
header('Content-Type: application/json');

$apiFile = 'trx_api.json';

function getApiUrl($file) {
    if (!file_exists($file)) {
        $default = ['apiUrl' => 'https://api.coingecko.com/api/v3/simple/price?ids=tron&vs_currencies=usd'];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default['apiUrl'];
    }
    $data = json_decode(file_get_contents($file), true);
    return $data['apiUrl'] ?? 'https://api.coingecko.com/api/v3/simple/price?ids=tron&vs_currencies=usd';
}

echo json_encode(['apiUrl' => getApiUrl($apiFile)]);
?>