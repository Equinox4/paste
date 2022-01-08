<?php
require_once '../RedisConnection.php';

if (empty($_GET['id'])) {
    header('Location: /');
    exit(0);
}

$display_modes = [ 0 => 'raw', 1 => 'source_code' ];

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

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <title>Paste - Render</title>

    <link href="data:;," rel="icon"/>
    <link rel="stylesheet" href="/assets/css/render.css"/>
</head>
<body>
    <pre><?= htmlspecialchars($content) ?></pre>
    <hr/>
    <p>Lien direct:
        <code class="selectable">https://paste.mjollnir.fr/v/<?= htmlspecialchars($key) ?></code>
    </p>
    <p>Texte brut:
        <code class="selectable">https://paste.mjollnir.fr/r/<?= htmlspecialchars($key) ?></code>
    </p>
    <p>Ce paste expire dans: <?= $redis->ttl($key) >= 0 ? gmdate("H\Hi:s", $redis->ttl($key)) : 'longtemps' ?></p>
    <p>
        <a href="/">[ Accueil ]</a>
    </p>
    <!-- TODO ajouter differents modes pour render: source code, raw -->
</body>
</html>
