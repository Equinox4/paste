<?php
declare(strict_types = 1);
require_once dirname(__DIR__) . '/RedisConnection.php';

function throwError($msg)
{
	header('Content-Type: text/plain; charset=UTF-8');
	echo $msg;
	exit(0);
}

if (empty($_POST['content']) || empty($_POST['duration']))
{
	header('Location: /');
	exit(0);
}

const MAX_CONTENT_LEN      = 100_000; // characters
const MAX_CUSTOM_ID_LEN    = 10;      // characters
const DEFAULT_RAND_ID_LEN  = 5;       // bytes
const ALLOWED_DURATIONS    = [ 3_600, 7_200, 43_200, 86_400 ]; // seconds

const UTF8 = 'UTF-8';
const UTF16 = 'UTF-16';

const INTERNAL_ERR        = 'Erreur interne, merci de bien vouloir réessayer plus tard.';
const CONTENT_LEN_ERR     = 'Le contenu du document est trop long, la limite est de ' . MAX_CONTENT_LEN . ' caractères.';
const CUSTOM_ID_LEN_ERR   = 'Identifiant personnalisé trop long, la limite est de ' . MAX_CUSTOM_ID_LEN . ' caractères.';
const DURATION_ERR        = 'La durée de vie sélectionnée pour le document n\'est pas valide.';
const CREATION_FAILED_ERR = 'La création du document a échoué :( Merci de réessayer plus tard.';

$post_content  = (string) $_POST['content'];
$post_duration = (int) $_POST['duration'];

// Calculate textarea length from the HTML spec
$post_content_utf16 = mb_convert_encoding($post_content, UTF16, UTF8);
if (!$post_content_utf16)
{
	throwError(INTERNAL_ERR);
}

$len_content = strlen($post_content_utf16) / 2 - mb_substr_count($post_content, "\r\n", UTF8);

if ($len_content > MAX_CONTENT_LEN)
{
	throwError(CONTENT_LEN_ERR);
}

if (!in_array($post_duration, ALLOWED_DURATIONS))
{
	throwError(DURATION_ERR);
}

if (empty($_POST['custom_id']))
{
	$content_id = bin2hex(random_bytes(DEFAULT_RAND_ID_LEN));
}
else
{
	$content_id = (string) $_POST['custom_id'];
	$content_id_utf16 = mb_convert_encoding($content_id, UTF16, UTF8);
	if (!$content_id_utf16)
	{
		throwError(INTERNAL_ERROR);
	}

	$len_content_id = strlen($content_id_utf16) / 2;
	if ($len_content_id > MAX_CUSTOM_ID_LEN)
	{
		throwError(CUSTOM_ID_LEN_ERR);
	}
}

$redis_user_env = getenv('REDIS_USER');
$redis_password_env = getenv('REDIS_PASSWORD');

$redis_connection = RedisConnection::getInstance();
if ($redis_user_env && $redis_password_env)
{
	$redis_connection->setCredentials($redis_user_env, $redis_password_env);
}

$redis = $redis_connection->connect();
$can_create = $redis->setNx($content_id, $post_content);
if (!$can_create)
{
	throwError(CREATION_FAILED_ERR);
}

$redis->expire($content_id, $post_duration);

header('Location: /v/' . urlencode($content_id));
exit(0);
