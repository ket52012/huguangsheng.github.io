<?php
header('Content-Type: application/json');

$announcementFile = 'announcement.json';

function getAnnouncement($file) {
    if (!file_exists($file)) {
        $default = ['content' => '欢迎使用胡广生代付，更高效 更专业'];
        file_put_contents($file, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    $data = json_decode(file_get_contents($file), true);
    return $data;
}

echo json_encode(getAnnouncement($announcementFile));
?>