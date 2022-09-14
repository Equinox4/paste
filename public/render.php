<?php
require_once '../RedisConnection.php';

if (empty($_GET['id'])) {
    header('Location: /');
    exit(0);
}

$redis_user_env = getenv('REDIS_USER') | null;
$redis_password_env = getenv('REDIS_PASSWORD') | null;
$redis = RedisConnection::getInstance(username: $redis_user_env, password: $redis_password_env)->getRedis();

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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <title>Paste - Render</title>

    <link href="data:;," rel="icon"/>
    <link rel="stylesheet" integrity="sha384-3l9xqGs/MbAG97dTUtXt3vCDu/5wOhj/N/Nkl1vOyMTi4OhGna6CD+8jBDsQUpao" href="/assets/css/render.css"/>
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
    <p>Ce document expire dans: <?= $redis->ttl($key) >= 0 ? gmdate("H\Hi:s", $redis->ttl($key)) : 'longtemps' ?></p>
    <p>
        <a href="/">[ Accueil ]</a>
    </p>
</body>
</html>
