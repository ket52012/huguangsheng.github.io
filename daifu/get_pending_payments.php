<?php
header('Content-Type: application/json');

$pendingFile = 'pending_payments.json';
$bossFile = 'boss_data.json';

function getData($file) {
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

$pending = getData($pendingFile);
$bosses = getData($bossFile);
$bossMap = array_column($bosses, 'name', 'id');

$result = [];
foreach ($pending as $id => $payment) {
    $result[] = [
        'id' => $id,
        'boss_name' => $bossMap[$payment['boss_id']] ?? '未知',
        'amount' => $payment['amount']
    ];
}

echo json_encode($result);
?>