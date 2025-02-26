<?php
session_start();
if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin']) {
    header('Location: admin.php');
    exit;
}

$pages = [
    'aitech.html' => ['title' => '实用AI黑科技', 'items' => 'button-list', 'fields' => ['text' => '名称', 'href' => '链接']],
    'giftcard.html' => ['title' => '代充和礼品卡', 'items' => null, 'fields' => ['content' => '描述']],
    'ipnodes.html' => ['title' => '各国家地区IP节点', 'items' => null, 'fields' => ['content' => '描述']],
    'maintenance.html' => ['title' => '搭建维护', 'items' => null, 'fields' => ['content' => '描述']],
    'software.html' => ['title' => '实用软件推荐', 'items' => 'button-list', 'fields' => ['text' => '名称', 'href' => '链接']],
    'tbd.html' => ['title' => '待补充项目', 'items' => 'button-list', 'fields' => ['text' => '项目名称', 'href' => '链接']]
];

$selectedPage = isset($_GET['page']) && array_key_exists($_GET['page'], $pages) ? $_GET['page'] : 'tbd.html';
$pageConfig = $pages[$selectedPage];
// 使用绝对路径确保正确指向根目录
$filePath = dirname(__DIR__) . '/' . $selectedPage; // 假设 daifu 在根目录下，即 /www/wwwroot/胡广生.com/tbd.html

// 调试：检查文件路径和权限
$debugInfo = '';
if (!file_exists($filePath)) {
    $debugInfo = "文件 $filePath 不存在，将创建默认文件。";
    $defaultHtml = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$pageConfig['title']}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: url('https://images.unsplash.com/photo-1538370965046-79c0d6907d47?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80') no-repeat center center fixed; background-size: cover; }
        .content { max-width: 800px; margin: 0 auto; padding: 20px; background-color: rgba(255, 255, 255, 0.9); border-radius: 5px; }
        .button-list { margin-top: 20px; }
        .link-button { display: block; padding: 10px; margin: 5px 0; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .link-button:hover { background-color: #0056b3; }
        .nav-buttons { margin-top: 20px; }
        .nav-button { display: inline-block; padding: 10px 20px; margin: 5px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .nav-button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="content">
        <h1>{$pageConfig['title']}</h1>
        <div class="button-list">
        </div>
        <div class="nav-buttons">
            <a href="./index.html" class="nav-button">返回主页面</a>
            <a href="./index.html" class="nav-button">返回上一层</a>
        </div>
    </div>
</body>
</html>
HTML;
    if (file_put_contents($filePath, $defaultHtml) === false) {
        $debugInfo .= " 创建默认文件失败，请检查写权限。";
    } else {
        $html = $defaultHtml;
    }
} elseif (!is_writable($filePath)) {
    $debugInfo = "文件 $filePath 不可写，请检查权限（需要644或更高）。";
    $html = file_get_contents($filePath);
} else {
    $html = file_get_contents($filePath);
}

function parseItems($html, $class) {
    $items = [];
    if (preg_match("/<div class=\"$class\">(.*?)<\/div>/s", $html, $match)) {
        preg_match_all('/<a[^>]+href=["\'](.*?)["\'][^>]*>(.*?)<\/a>/s', $match[1], $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $items[] = ['href' => $m[1], 'text' => trim($m[2])];
        }
    }
    return $items;
}

function parseContent($html) {
    if (preg_match('/<p>(.*?)<\/p>/s', $html, $match)) {
        return trim($match[1]);
    }
    return '';
}

function updateHtml($html, $items, $content, $class) {
    if ($class && $items !== null) {
        $newList = '<div class="' . $class . '">' . PHP_EOL;
        foreach ($items as $item) {
            $href = htmlspecialchars($item['href'] ?? '#');
            $text = htmlspecialchars($item['text']);
            $newList .= "            <a href=\"$href\" class=\"link-button\">$text</a>" . PHP_EOL;
        }
        $newList .= '        </div>';
        if (preg_match("/<div class=\"$class\">(.*?)<\/div>/s", $html)) {
            $html = preg_replace("/<div class=\"$class\">(.*?)<\/div>/s", $newList, $html);
        } else {
            $html = preg_replace('/(<h1>.*?<\/h1>)/', "$1\n$newList", $html);
        }
    }
    if ($content !== null) {
        $newContent = "<p>" . htmlspecialchars($content) . "</p>";
        if (preg_match('/<p>.*?<\/p>/s', $html)) {
            $html = preg_replace('/<p>.*?<\/p>/s', $newContent, $html);
        } else {
            $html = preg_replace('/(<h1>.*?<\/h1>)/', "$1\n$newContent", $html);
        }
    }
    return $html;
}

$items = $pageConfig['items'] ? parseItems($html, $pageConfig['items']) : null;
$content = $pageConfig['items'] ? null : parseContent($html);

if (isset($_POST['save'])) {
    $items = $pageConfig['items'] ? [] : null;
    $content = $pageConfig['items'] ? null : $_POST['content'];
    if ($items !== null) {
        foreach ($_POST['items'] as $i => $itemData) {
            $item = [];
            foreach ($pageConfig['fields'] as $field => $label) {
                $item[$field] = $itemData[$field] ?? ($field === 'href' ? '#' : '');
            }
            $items[] = $item;
        }
    }
    $newHtml = updateHtml($html, $items, $content, $pageConfig['items']);
    if (file_put_contents($filePath, $newHtml) === false) {
        $debugInfo = "保存文件 $filePath 失败，请检查写权限。";
    } else {
        $debugInfo = "已保存 $selectedPage";
        $html = $newHtml;
        $items = $pageConfig['items'] ? parseItems($html, $pageConfig['items']) : null; // 确保编辑页面刷新
    }
}

if (isset($_POST['add_item']) && $pageConfig['items']) {
    $items[] = array_fill_keys(array_keys($pageConfig['fields']), '');
}

if (isset($_POST['delete_item']) && $pageConfig['items']) {
    unset($items[(int)$_POST['delete_item']]);
    $items = array_values($items);
    $newHtml = updateHtml($html, $items, null, $pageConfig['items']);
    if (file_put_contents($filePath, $newHtml) === false) {
        $debugInfo = "删除条目并保存文件 $filePath 失败，请检查写权限。";
    } else {
        $debugInfo = "已删除条目并保存 $selectedPage";
        $html = $newHtml;
        $items = parseItems($html, $pageConfig['items']);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑前端页面</title>
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
            <a href="edit_password.php" class="nav-item">前端密码</a>
            <a href="edit_pages.php" class="nav-item active">前端页面</a>
            <a href="admin.php?logout=1" class="nav-item logout">退出登录</a>
        </div>
        <div class="main-content">
            <h2>编辑前端页面</h2>
            <?php if ($debugInfo): ?><p class="message"><?php echo $debugInfo; ?></p><?php endif; ?>
            <form method="get">
                <label>选择页面：</label>
                <select name="page" onchange="this.form.submit()">
                    <?php foreach ($pages as $page => $config): ?>
                        <option value="<?php echo $page; ?>" <?php echo $page === $selectedPage ? 'selected' : ''; ?>><?php echo $config['title']; ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <form method="post">
                <h3>编辑 <?php echo $pageConfig['title']; ?></h3>
                <?php if ($pageConfig['items']): ?>
                    <?php if (empty($items)): ?>
                        <p>暂无项目</p>
                    <?php else: ?>
                        <?php foreach ($items as $i => $item): ?>
                            <div class="item-block">
                                <?php foreach ($pageConfig['fields'] as $field => $label): ?>
                                    <label><?php echo $label; ?>:</label>
                                    <input type="text" name="items[<?php echo $i; ?>][<?php echo $field; ?>]" value="<?php echo htmlspecialchars($item[$field]); ?>" required>
                                <?php endforeach; ?>
                                <button type="submit" name="delete_item" value="<?php echo $i; ?>" class="delete-btn">删除</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <button type="submit" name="add_item" class="action-btn">添加新项</button>
                <?php else: ?>
                    <label>内容:</label>
                    <textarea name="content" rows="5" required><?php echo htmlspecialchars($content); ?></textarea>
                <?php endif; ?>
                <input type="submit" name="save" value="保存">
            </form>
        </div>
    </div>
</body>
</html>