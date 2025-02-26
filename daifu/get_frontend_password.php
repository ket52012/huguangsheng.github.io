<?php
header('Content-Type: application/json');

$passwordFile = 'frontend_password.json';

function getPassword($file) {
    if (!file_exists($file)) {
        $default = ['password' => '123'];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    $data = json_decode(file_get_contents($file), true);
    return $data;
}

echo json_encode(getPassword($passwordFile));
?>