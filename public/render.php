<?php
require_once '../RedisConnection.php';

if (empty($_GET['id'])) {
	header('Location: /');
	exit(0);
}

$redis_user_env = getenv('REDIS_USER') | null;
$redis_password_env = getenv('REDIS_PASSWORD') | null;

$redis_connection = RedisConnection::getInstance();
if (!empty($redis_user_env)) {
    $redis_connection->setCredentials($redis_user_env, $redis_password_env);
}
$redis = $redis_connection->connect();

$key = $_GET['id'];

if (!$redis->exists($key)) {
	header('Location: /');
	exit(0);
}

$content = $redis->get($key);

if (!empty($_GET['mode']) && $_GET['mode'] === 'raw') {
	header('Content-Type: text/plain; charset=UTF-8');
	echo $content;
	exit(0);
}

$classic_link = 'https://paste.mjollnir.fr/v/' . htmlspecialchars($key);
$raw_link = 'https://paste.mjollnir.fr/r/' . htmlspecialchars($key);

// pour le $redis->ttl($key) - 1 plus bas: éviter l'affichage de 00H00 pour 24H

?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="utf-8"/>
	<title>Paste - Render</title>

	<link href="data:;," rel="icon"/>
	<link rel="stylesheet" integrity="sha384-qBxW2JkHPMxlr1uNQSBJ5dtYh/6zbe9EGQcPKxrdQxj3RQZG9SxSAdFfDAsl2l/j" href="/assets/css/render.css"/>
</head>
<body>
	<pre><?= htmlspecialchars($content) ?></pre>
	<hr/>
	<p>Lien direct:
		<a href="<?= $classic_link ?>"><?= $classic_link ?></a>
	</p>
	<p>Texte brut:
		<a href="<?= $raw_link ?>"><?= $raw_link ?></a>
	</p>
	<p>Ce document expire dans: <?= $redis->ttl($key) >= 0 ? gmdate("H\Hi:s", $redis->ttl($key) - 1) : 'longtemps' ?></p>
	<p>
		<a href="/">[ Accueil ]</a>
	</p>
</body>
</html>
