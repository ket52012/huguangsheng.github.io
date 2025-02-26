<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$adminPassword = '123';

if (isset($_POST['password']) && $_POST['password'] === $adminPassword) {
    $_SESSION['loggedin'] = true;
    $_SESSION['message'] = "登录成功！";
}

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    $message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
    unset($_SESSION['message']);
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>管理员登录</title>
        <link rel="stylesheet" href="admin_styles.css">
    </head>
    <body>
        <div class="login-container">
            <h2>管理员登录</h2>
            <form method="post">
                <input type="password" name="password" placeholder="请输入密码" required>
                <br>
                <input type="submit" value="登录">
            </form>
            <p class="message">$message</p>
        </div>
    </body>
    </html>
HTML;
    exit;
}

if (isset($_GET['logout'])) {
    $_SESSION['message'] = "已退出登录！";
    session_destroy();
    header('Location: admin.php');
    exit;
}

$statsFile = 'stats.json';
function getStats($file) {
    if (!file_exists($file)) {
        $default = ['visits' => 0, 'likes' => 0];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    return json_decode(file_get_contents($file), true);
}
$stats = getStats($statsFile);

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后端管理</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>管理选项</h3>
            <a href="admin.php" class="nav-item active">首页</a>
            <a href="edit_boss.php" class="nav-item">老板明细</a>
            <a href="edit_api.php" class="nav-item">汇率API</a>
            <a href="edit_announcement.php" class="nav-item">公告</a>
            <a href="edit_password.php" class="nav-item">前端密码</a>
            <a href="edit_pages.php" class="nav-item">前端页面</a>
            <a href="?logout=1" class="nav-item logout">退出登录</a>
        </div>
        <div class="main-content">
            <h2>欢迎回来</h2>
            <?php if ($message): ?>
                <p class="message"><?php echo $message; ?></p>
            <?php endif; ?>
            <h3>统计信息</h3>
            <table class="stats-table">
                <tr><th>总共代付次数</th><td><?php echo $stats['visits']; ?></td></tr>
                <tr><th>点赞数</th><td><?php echo $stats['likes']; ?></td></tr>
            </table>
        </div>
        <div class="floating-window" id="pendingPayments">
            <h3>待确认代付请求</h3>
            <table id="pendingTable">
                <tr><th>老板姓名</th><th>金额 (USDT)</th><th>操作</th></tr>
            </table>
        </div>
    </div>
    <audio id="notificationSound" src="https://www.soundjay.com/buttons/beep-01a.mp3" preload="auto"></audio>
    <script>
        let lastPendingCount = 0;
        function fetchPendingPayments() {
            fetch('./get_pending_payments.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('pendingTable').getElementsByTagName('tbody')[0] || document.getElementById('pendingTable').appendChild(document.createElement('tbody'));
                    tbody.innerHTML = '<tr><th>老板姓名</th><th>金额 (USDT)</th><th>操作</th></tr>';
                    if (data.length > 0) {
                        data.forEach(payment => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${payment.boss_name || '未知'}</td>
                                <td>${payment.amount}</td>
                                <td>
                                    <form method="post" action="admin.php" style="display:inline;">
                                        <input type="hidden" name="payment_id" value="${payment.id}">
                                        <input type="submit" name="confirm_payment" value="确认" class="action-btn">
                                    </form>
                                    <form method="post" action="admin.php" style="display:inline;">
                                        <input type="hidden" name="payment_id" value="${payment.id}">
                                        <input type="submit" name="cancel_payment" value="取消" class="cancel-btn" onclick="return confirm('确认取消此代付请求吗？');">
                                    </form>
                                </td>
                            `;
                            tbody.appendChild(row);
                        });
                        if (data.length > lastPendingCount) {
                            document.getElementById('notificationSound').play().catch(error => console.log('提示音播放失败:', error));
                        }
                        lastPendingCount = data.length;
                    } else {
                        lastPendingCount = 0;
                    }
                });
        }
        document.addEventListener('DOMContentLoaded', () => {
            fetchPendingPayments();
            setInterval(fetchPendingPayments, 5000);
        });
    </script>
</body>
</html>
<?php
if (isset($_POST['confirm_payment']) || isset($_POST['cancel_payment'])) {
    $pendingFile = 'pending_payments.json';
    $bossFile = 'boss_data.json';
    $pending = json_decode(file_exists($pendingFile) ? file_get_contents($pendingFile) : '[]', true);
    $bosses = json_decode(file_exists($bossFile) ? file_get_contents($bossFile) : '[]', true);
    $paymentId = (int)$_POST['payment_id'];

    if (isset($_POST['confirm_payment']) && isset($pending[$paymentId])) {
        foreach ($bosses as &$boss) {
            if ($boss['id'] == $pending[$paymentId]['boss_id']) {
                $boss['amount'] = round($boss['amount'] - (float)$pending[$paymentId]['amount'], 1);
                $stats['visits'] += 1;
                file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));
                unset($pending[$paymentId]);
                file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));
                file_put_contents($bossFile, json_encode($bosses, JSON_PRETTY_PRINT));
                $_SESSION['message'] = "已确认代付请求（ID: $paymentId）";
                break;
            }
        }
    } elseif (isset($_POST['cancel_payment']) && isset($pending[$paymentId])) {
        unset($pending[$paymentId]);
        file_put_contents($pendingFile, json_encode($pending, JSON_PRETTY_PRINT));
        $_SESSION['message'] = "已取消代付请求（ID: $paymentId）";
    }
    header("Location: admin.php");
    exit;
}
?>