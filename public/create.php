<?php
require_once '../RedisConnection.php';

if (empty($_POST['content']) || empty($_POST['duration'])) {
    header('Location: /');
    exit(0);
}

const MAX_CONTENT_LEN = 100_000; // in characters
const MAX_CUSTOM_ID_LEN = 10;
const ALLOWED_DURATIONS = [ 3_600, 7_200, 43_200, 86_400 ]; // in seconds

$post_content = $_POST['content'];
$post_duration = $_POST['duration'];

if (mb_strlen($post_content) > MAX_CONTENT_LEN) {
    echo 'Too long content.';
    exit(0);
}

if (!in_array($post_duration, ALLOWED_DURATIONS)) {
    echo 'Invalid duration.';
    exit(0);
}

if (empty($_POST['custom_id'])) {
    $content_id = bin2hex(random_bytes(5));
}
else {
    $content_id = $_POST['custom_id'];
    if (strlen($content_id) > MAX_CUSTOM_ID_LEN) {
        echo 'Too long id.';
        exit(0);
    }
}

$redis = RedisConnection::getInstance()->getRedis();
$can_create = $redis->setNx($content_id, $post_content);
if (!$can_create) {
    echo 'Failed to create paste :( Please retry';
    exit(0);
}

$redis->expire($content_id, $post_duration);

header("Location: /v/$content_id");
exit(0);
