<?php
header('Content-Type: application/json');

$apiFile = 'usd_cny_api.json';

function getApiData($file) {
    if (!file_exists($file)) {
        $default = [
            'apiUrl' => 'https://v6.exchangerate-api.com/v6/YOUR_API_KEY/latest/USD',
            'defaultRate' => 7.22
        ];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    $data = json_decode(file_get_contents($file), true);
    return [
        'apiUrl' => $data['apiUrl'] ?? 'https://v6.exchangerate-api.com/v6/YOUR_API_KEY/latest/USD',
        'defaultRate' => $data['defaultRate'] ?? 7.22
    ];
}

echo json_encode(getApiData($apiFile));
?>