<?php
require_once dirname(__DIR__) . '/RedisConnection.php';

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
	<title>Texte ephémère | Consulter un document</title>

	<meta name="application-name" content="Texte ephémère"/>
    <meta name="author"           content="Mjöllnir"/>
    <meta name="description"      content="Service en ligne vous permettant de créer et de partager des documents texte sur une courte durée."/>
    <meta name="theme-color"      content="#33b8ff"/>
	<meta name="viewport"         content="width=device-width, initial-scale=1">

	<link href="data:;base64,iVBORw0KGgo=" rel="icon"/>
	<link rel="stylesheet" integrity="sha384-hxdtynUOO7Tr/2atQIx2xbhzKbVvGcXKKIAylVtB5GPjhVxfF5rW4jd20bFavGt1" href="/assets/css/render.css"/>
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
