<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: admin.php');
    exit;
}

$passwordFile = 'frontend_password.json';
$passwordData = json_decode(file_exists($passwordFile) ? file_get_contents($passwordFile) : '{"password":"123"}', true);

if (isset($_POST['update_password'])) {
    $passwordData = ['password' => $_POST['password']];
    file_put_contents($passwordFile, json_encode($passwordData, JSON_PRETTY_PRINT));
    $_SESSION['message'] = "前端密码已更新";
    header('Location: edit_password.php');
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
    <title>编辑前端密码</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>管理选项</h3>
            <a href="admin.php" class="nav-item">首页</a>
            <a href="edit_boss.php" class="nav-item">老板明细</a>
            <a href="edit_api.php" class="nav-item">汇率API</a>
            <a href="edit_announcement.php" class="nav-item">公告</a>
            <a href="edit_password.php" class="nav-item active">前端密码</a>
            <a href="edit_pages.php" class="nav-item">前端页面</a>
            <a href="admin.php?logout=1" class="nav-item logout">退出登录</a>
        </div>
        <div class="main-content">
            <h2>编辑前端密码</h2>
            <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
            <form method="post">
                <label>新密码:</label>
                <input type="text" name="password" value="<?php echo htmlspecialchars($passwordData['password']); ?>" required>
                <input type="submit" name="update_password" value="保存">
            </form>
        </div>
    </div>
</body>
</html>