<?php
declare(strict_types = 1);
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

$key = urldecode($_GET['id']);

if (!$redis->exists($key)) {
	http_response_code(404);
	echo 'Ce document est introuvable :/';
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
	<title>Texte ephémère | Visualiser un document</title>

	<meta name="application-name" content="Texte ephémère"/>
	<meta name="author"           content="Mjöllnir"/>
	<meta name="description"      content="Service en ligne de création et de partage de documents textuels sur une courte durée."/>
	<meta name="theme-color"      content="#33b8ff"/>
	<meta name="viewport"         content="width=device-width, initial-scale=1">

	<meta property="og:site_name"   content="Texte ephémère">
	<meta property="og:title"       content="Visualiser un document">
	<meta property="og:description" content="Service en ligne de création et de partage de documents textuels sur une courte durée.">
	<meta property="og:image"       content="/assets/images/icons/paste_icon_128.png">
	<meta property="og:type"        content="website">
	<meta property="og:url"         content="https://paste.mjollnir.fr">

	<link href="/assets/images/icons/paste_icon_16.png" rel="icon"/>
	<link rel="stylesheet" integrity="sha384-hxdtynUOO7Tr/2atQIx2xbhzKbVvGcXKKIAylVtB5GPjhVxfF5rW4jd20bFavGt1" href="/assets/css/render.css"/>
</head>
<body>
	<p>Lien direct:
		<a href="<?= $classic_link ?>"><?= $classic_link ?></a>
	</p>
	<p>Texte brut:
		<a href="<?= $raw_link ?>"><?= $raw_link ?></a>
	</p>
	<p>Ce document expire dans: <time datetime="<?= gmdate("H:i:s", $time_left) ?>"><?= gmdate("H\Hi:s", $time_left) ?></time></p>
	<p>
		<a href="/">[ Accueil ]</a>
	</p>
	<hr/>
	<pre><?= htmlspecialchars($content) ?></pre>
</body>
</html>
