<?php
require_once 'RedisConnection.php';

if (empty($_POST['content']) || empty($_POST['duration'])) {
    header('Location: /');
    exit(0);
}

$redis = RedisConnection::getInstance()->getRedis();

$max_content_len = 100000; // in characters
$max_custom_id_len = 10;
$allowed_durations = [ 3600, 7200, 43200, 86400 ]; // in seconds

$post_content = $_POST['content'];
$post_duration = $_POST['duration'];

if (strlen($post_content) > $max_content_len) {
    echo 'Too long content.';
    exit(0);
}

if (!in_array($post_duration, $allowed_durations)) {
    echo 'Invalid duration.';
    exit(0);
}

// beurk refaire ça

if (!empty($_POST['custom_id'])) {
    $post_custom_id = $_POST['custom_id'];
    if (strlen($post_custom_id) > $max_custom_id_len) {
        echo 'Too long id';
        exit(0);
    }

    $can_create = $redis->setNx($post_custom_id, $post_content);
    if (!$can_create) {
        echo 'Failed to create paste :(';
        exit(0);
    }

    $redis->expire($post_custom_id, $post_duration);

    header("Location: /v/$post_custom_id");
    exit(0);
}

$try_count = 1;
do {
    $key = bin2hex(random_bytes(4));
    $can_create = $redis->setNx($key, $post_content);

    $try_count++;
} while (!$can_create && $try_count <= 3);

if (!$can_create) {
    echo 'Failed to create paste :( Please retry with an other id.';
    exit(0);
}

$redis->expire($key, $post_duration);

header("Location: /v/$key");
exit(0);
