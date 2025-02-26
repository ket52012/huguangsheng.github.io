<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: admin.php');
    exit;
}

$usdCnyFile = 'usd_cny_api.json';
$trxFile = 'trx_api.json';
$usdCnyData = json_decode(file_exists($usdCnyFile) ? file_get_contents($usdCnyFile) : '{"apiUrl":"https://v6.exchangerate-api.com/v6/YOUR_API_KEY/latest/USD","defaultRate":7.22}', true);
$trxData = json_decode(file_exists($trxFile) ? file_get_contents($trxFile) : '{"apiUrl":"https://api.coingecko.com/api/v3/simple/price?ids=tron&vs_currencies=usd"}', true);

if (isset($_POST['update_api'])) {
    $usdCnyData = ['apiUrl' => $_POST['usd_cny_api'], 'defaultRate' => (float)$_POST['usd_cny_default_rate']];
    $trxData = ['apiUrl' => $_POST['trx_api']];
    file_put_contents($usdCnyFile, json_encode($usdCnyData, JSON_PRETTY_PRINT));
    file_put_contents($trxFile, json_encode($trxData, JSON_PRETTY_PRINT));
    $_SESSION['message'] = "汇率API已更新";
    header('Location: edit_api.php');
    exit;
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑汇率API</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>管理选项</h3>
            <a href="admin.php" class="nav-item">首页</a>
            <a href="edit_boss.php" class="nav-item">老板明细</a>
            <a href="edit_api.php" class="nav-item active">汇率API</a>
            <a href="edit_announcement.php" class="nav-item">公告</a>
            <a href="edit_password.php" class="nav-item">前端密码</a>
            <a href="edit_pages.php" class="nav-item">前端页面</a>
            <a href="admin.php?logout=1" class="nav-item logout">退出登录</a>
        </div>
        <div class="main-content">
            <h2>编辑汇率API</h2>
            <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
            <form method="post">
                <h3>USD/CNY API</h3>
                <label>API URL:</label>
                <input type="text" name="usd_cny_api" value="<?php echo $usdCnyData['apiUrl']; ?>" required>
                <label>默认汇率:</label>
                <input type="number" name="usd_cny_default_rate" value="<?php echo $usdCnyData['defaultRate']; ?>" step="0.01" required>
                <h3>TRX/USDT API</h3>
                <label>API URL:</label>
                <input type="text" name="trx_api" value="<?php echo $trxData['apiUrl']; ?>" required>
                <input type="submit" name="update_api" value="保存">
            </form>
        </div>
    </div>
</body>
</html>