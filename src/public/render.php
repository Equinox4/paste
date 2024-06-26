<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/RedisConnection.php';

if (empty($_GET['id'])) {
	header('Location: /');
	exit(0);
}

$redis_user_env = getenv('REDIS_USER');
$redis_password_env = getenv('REDIS_PASSWORD');

$redis_connection = RedisConnection::getInstance();
if ($redis_user_env && $redis_password_env) {
	$redis_connection->setCredentials($redis_user_env, $redis_password_env);
}

$key = urldecode($_GET['id']);
$redis = $redis_connection->connect();
$content = $redis->get($key);
if (!$content) {
	http_response_code(404);
	echo 'Ce document est introuvable :/';
	exit(0);
}

if (!empty($_GET['mode']) && $_GET['mode'] === 'raw') {
	header('Content-Type: text/plain; charset=UTF-8');
	echo $content;
	exit(0);
}

$classic_link = "https://{$_SERVER['SERVER_NAME']}/v/" . htmlspecialchars($key);
$raw_link = "https://{$_SERVER['SERVER_NAME']}/r/" . htmlspecialchars($key);
// - 1: avoid display of 00H00 for 24H
$time_left = $redis->ttl($key) - 1;
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

	<link rel="stylesheet"               href="/assets/css/main.css"   integrity=""/>
	<link rel="stylesheet"               href="/assets/css/render.css" integrity=""/>
	<link rel="stylesheet" media="print" href="/assets/css/print_main.css"    integrity=""/>
	<link rel="stylesheet" media="print" href="/assets/css/print_render.css"  integrity=""/>
</head>
<body>
	<header>
		<h1>Texte ephémère</h1>
		<nav><a href="/">[ Accueil ]</a></nav>
	</header>
	<main>
		<section id="document-infos">
			<p>Lien direct:
				<a href="<?= $classic_link ?>"><?= $classic_link ?></a>
			</p>
			<p>Texte brut:
				<a href="<?= $raw_link ?>"><?= $raw_link ?></a>
			</p>
			<p>Ce document expire dans: <time datetime="<?= gmdate("H:i:s", $time_left) ?>"><?= gmdate("H\Hi:s", $time_left) ?></time></p>
		</section>
		<section id="document-content">
			<pre><?= htmlspecialchars($content) ?></pre>
		</section>
	</main>
	<footer>Propulsé par PHP 8 & Redis &bull; Conçu avec &hearts; en &#x1f1eb;&#x1f1f7; &bull; <a href="https://github.com/Equinox4/paste">Code source</a> &bull; <a href="https://www.mjollnir.fr/legal_information.html">Infomations légales</a>
	</footer>
</body>
</html>
