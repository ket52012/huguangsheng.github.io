<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: admin.php');
    exit;
}

$announcementFile = 'announcement.json';
$announcementData = json_decode(file_exists($announcementFile) ? file_get_contents($announcementFile) : '{"content":"欢迎使用胡广生代付，更高效 更专业"}', true);

if (isset($_POST['update_announcement'])) {
    $announcementData = ['content' => $_POST['content']];
    file_put_contents($announcementFile, json_encode($announcementData, JSON_PRETTY_PRINT));
    $_SESSION['message'] = "公告已更新";
    header('Location: edit_announcement.php');
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
    <title>编辑公告</title>
    <link rel="stylesheet" href="admin_styles.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>管理选项</h3>
            <a href="admin.php" class="nav-item">首页</a>
            <a href="edit_boss.php" class="nav-item">老板明细</a>
            <a href="edit_api.php" class="nav-item">汇率API</a>
            <a href="edit_announcement.php" class="nav-item active">公告</a>
            <a href="edit_password.php" class="nav-item">前端密码</a>
            <a href="edit_pages.php" class="nav-item">前端页面</a>
            <a href="admin.php?logout=1" class="nav-item logout">退出登录</a>
        </div>
        <div class="main-content">
            <h2>编辑公告</h2>
            <?php if ($message): ?><p class="message"><?php echo $message; ?></p><?php endif; ?>
            <form method="post">
                <label>公告内容:</label>
                <textarea name="content" rows="5" required><?php echo htmlspecialchars($announcementData['content']); ?></textarea>
                <input type="submit" name="update_announcement" value="保存">
            </form>
        </div>
    </div>
</body>
</html>