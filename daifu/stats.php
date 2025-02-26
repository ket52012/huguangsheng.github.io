<?php
header('Content-Type: application/json');

$statsFile = 'stats.json';

function getStats() {
    global $statsFile;
    if (!file_exists($statsFile)) {
        $initialData = ['visits' => 0, 'likes' => 0];
        file_put_contents($statsFile, json_encode($initialData));
    }
    return json_decode(file_get_contents($statsFile), true);
}

function saveStats($data) {
    global $statsFile;
    file_put_contents($statsFile, json_encode($data));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = [];

switch ($action) {
    case 'get':
        $stats = getStats();
        $stats['visits']++;
        saveStats($stats);
        $response = $stats;
        break;
    case 'like':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $stats = getStats();
            $stats['likes']++;
            saveStats($stats);
            $response = $stats;
        } else {
            $response = ['error' => 'Invalid request'];
        }
        break;
    default:
        $response = ['error' => 'Invalid action'];
}

echo json_encode($response);
?>