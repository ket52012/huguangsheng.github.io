<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: admin.php');
    exit;
}

$bossFile = 'boss_data.json';
$bosses = json_decode(file_exists($bossFile) ? file_get_contents($bossFile) : '[]', true);

if (isset($_POST['add_boss'])) {
    $newId = empty($bosses) ? 1 : max(array_column($bosses, 'id')) + 1;
    $bosses[] = ['id' => $newId, 'name' => $_POST['boss_name'], 'amount' => round((float)$_POST['boss_amount'], 1)];
    file_put_contents($bossFile, json_encode($bosses, JSON_PRETTY_PRINT));
    $_SESSION['message'] = "已添加老板：{$_POST['boss_name']}";
    header('Location: edit_boss.php');
    exit;
}

if (isset($_POST['adjust_boss'])) {
    foreach ($bosses as &$boss) {
        if ($boss['id'] == $_POST['boss_id']) {
            $adjustAmount = (float)$_POST['adjust_amount'];
            if ($_POST['action'] === 'increase') {
                $boss['amount'] += $adjustAmount;
            } elseif ($_POST['action'] === 'decrease' && $boss['amount'] >= $adjustAmount) {
                $boss['amount'] -= $adjustAmount;
            }
            $boss['amount'] = round($boss['amount'], 1);
            file_put_contents($bossFile, json_encode($bosses, JSON_PRETTY_PRINT));
            $_SESSION['message'] = "已调整 {$boss['name']} 的余额";
            break;
        }
    }
    header('Location: edit_boss.php');
    exit;
}

if (isset($_POST['delete_boss'])) {
    $bosses = array_filter($bosses, fn($b) => $b['id'] != $_POST['boss_id']);
    file_put_contents($bossFile, json_encode(array_values($bosses), JSON_PRETTY_PRINT));
    $_SESSION['message'] = "已删除老板";
    header('Location: edit_boss.php');
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
    <title>编辑老板明细</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>管理选项</h3>
            <a href="admin.php" class="nav-item">首页</a>
            <a href="edit_boss.php" class="nav-item active">老板明细</a>
            <a href="edit_api.php" class="nav-item">汇率API</a>
            <a href="edit_announcement.php" class="nav-item">公告</a>
            <a href="edit_password.php" class="nav-item">前端密码</a>
            <a href="edit_pages.php" class="nav-item">前端页面</a>
            <a href="admin.php?logout=1" class="nav-item logout">退出登录</a>
        </div>
        <div class="main-content">
            <h2>编辑老板明细</h2>
            <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
            <form method="post">
                <h3>添加新老板</h3>
                <label>老板姓名:</label>
                <input type="text" name="boss_name" placeholder="姓名" required maxlength="10" class="short-input">
                <label>初始余额:</label>
                <input type="number" name="boss_amount" placeholder="余额" step="0.01" required class="short-input">
                <input type="submit" name="add_boss" value="添加">
            </form>
            <h3>老板列表</h3>
            <table class="boss-list">
                <tr><th>ID</th><th>姓名</th><th>余额 (USDT)</th><th>操作</th></tr>
                <?php foreach ($bosses as $boss): ?>
                    <tr>
                        <td><?php echo $boss['id']; ?></td>
                        <td><?php echo $boss['name']; ?></td>
                        <td><?php echo number_format($boss['amount'], 1); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="boss_id" value="<?php echo $boss['id']; ?>">
                                <input type="number" name="adjust_amount" step="0.01" min="0" required class="short-input">
                                <input type="submit" name="adjust_boss" value="增加" class="action-btn" onclick="this.form.action.value='increase';">
                                <input type="hidden" name="action" value="increase">
                                <input type="submit" name="adjust_boss" value="减少" class="action-btn" onclick="this.form.action.value='decrease';">
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="boss_id" value="<?php echo $boss['id']; ?>">
                                <input type="submit" name="delete_boss" value="删除" class="action-btn delete-btn" onclick="return confirm('确认删除吗？');">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</body>
</html>