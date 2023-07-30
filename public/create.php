<?php
require_once dirname(__DIR__) . '/RedisConnection.php';

if (empty($_POST['content']) || empty($_POST['duration'])) {
	header('Location: /');
	exit(0);
}

const MAX_CONTENT_LEN      = 100_000; // characters
const MAX_CUSTOM_ID_LEN    = 10;      // characters
const DEFAULT_RAND_ID_LEN  = 5;       // bytes
const ALLOWED_DURATIONS    = [ 3_600, 7_200, 43_200, 86_400 ]; // seconds

$post_content  = $_POST['content'];
$post_duration = $_POST['duration'];

// Calculate textarea length from the HTML spec
$len_content = strlen(mb_convert_encoding($post_content, 'UTF-16', 'UTF-8')) / 2 - mb_substr_count($post_content, "\r\n", 'UTF-8');

if ($len_content > MAX_CONTENT_LEN) {
	echo 'Le contenu du document est trop long.';
	exit(0);
}

if (!in_array($post_duration, ALLOWED_DURATIONS)) {
	echo 'La durée de vie sélectionnée pour le document n\'est pas valide.';
	exit(0);
}

if (empty($_POST['custom_id'])) {
	$content_id = bin2hex(random_bytes(DEFAULT_RAND_ID_LEN));
}
else {
	$content_id = $_POST['custom_id'];
	$len_content_id = strlen(mb_convert_encoding($content_id, 'UTF-16', 'UTF-8')) / 2;
	if ($len_content_id > MAX_CUSTOM_ID_LEN) {
		echo 'Identifiant personnalisé trop long.';
		exit(0);
	}
}

$redis_user_env = getenv('REDIS_USER') | null;
$redis_password_env = getenv('REDIS_PASSWORD') | null;

$redis_connection = RedisConnection::getInstance();
if (!empty($redis_user_env)) {
	$redis_connection->setCredentials($redis_user_env, $redis_password_env);
}
$redis = $redis_connection->connect();

$can_create = $redis->setNx($content_id, $post_content);
if (!$can_create) {
	echo 'La création du document a échoué :( Merci de réessayer plus tard.';
	exit(0);
}

$redis->expire($content_id, $post_duration);

header('Location: /v/' . urlencode($content_id));
exit(0);
