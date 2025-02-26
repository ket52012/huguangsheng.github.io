<?php
$dir = '/www/wwwroot/xn--swtp71c7ji.com/daifu/';
$logDir = $dir . 'logs/'; // 日志目录
$threeMonthsAgo = strtotime('-3 months'); // 3 个月前的时间戳

// 创建日志目录（若不存在）
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 示例：清理 Nginx 日志（若有）
$nginxLog = '/www/wwwlogs/xn--swtp71c7ji.com.log';
$nginxErrorLog = '/www/wwwlogs/xn--swtp71c7ji.com.error.log';
if (file_exists($nginxLog) && filemtime($nginxLog) < $threeMonthsAgo) {
    rename($nginxLog, $logDir . 'nginx_access_' . date('Ymd_His', filemtime($nginxLog)) . '.log');
}
if (file_exists($nginxErrorLog) && filemtime($nginxErrorLog) < $threeMonthsAgo) {
    rename($nginxErrorLog, $logDir . 'nginx_error_' . date('Ymd_His', filemtime($nginxErrorLog)) . '.log');
}

// 清理日志目录中 3 个月前的文件
if (is_dir($logDir)) {
    $files = glob($logDir . '*');
    foreach ($files as $file) {
        if (filemtime($file) < $threeMonthsAgo) {
            unlink($file);
        }
    }
}

echo "清理完成！";
?>