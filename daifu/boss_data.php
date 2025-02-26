<?php
header('Content-Type: application/json');

$bossFile = 'boss_data.json';
$pendingFile = 'pending_payments.json';
$adminPassword = 'BACKEND_PASSWORD';

function getData($file) {
    if (!file_exists($file)) {
        $default = ($file === 'boss_data.json') ? [] : [];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function saveData($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = [];

switch ($action) {
    case 'get':
        $response = ['bosses' => getData($bossFile)];
        break;
    case 'confirm':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && $_POST['password'] === $adminPassword) {
            $bossId = (int)$_POST['boss_id'];
            $amount = (float)$_POST['amount'];
            $pending = getData($pendingFile);
            $paymentId = time();
            $pending[$paymentId] = ['boss_id' => $bossId, 'amount' => $amount];
            saveData($pendingFile, $pending);
            $response = ['success' => true, 'message' => '代付请求已提交，等待后端确认', 'boss_id' => $bossId, 'amount' => $amount];
        } else {
            $response = ['success' => false, 'error' => '密码错误'];
        }
        break;
    default:
        $response = ['error' => 'Invalid action'];
}

echo json_encode($response);
?>