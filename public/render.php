<?php 
require_once '../RedisConnection.php';

$redis = RedisConnection::getInstance()->getRedis();

if (empty($_GET['id'])) {
    header('Location: /');
    exit(0);
}

$display_modes = [ 0 => 'raw', 1 => 'source_code' ];

$key = $_GET['id'];

if (!$redis->exists($key)) {
    header('Location: /');
    exit(0);
}

$content = $redis->get($key);

if (!empty($_GET['mode']) && $_GET['mode'] === 'raw') {
    echo htmlspecialchars($content); // faire un vrai mode raw
    exit(0);
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <title>Paste - Render</title>

    <link href="data:;," rel="icon"/>
</head>
<body>
<pre>
<?= htmlspecialchars($content) ?>
</pre>
<hr/>
<p>Lien cours: <code>https://paste.mjollnir.fr/v/<?= $key ?></code></p>
<p>Texte brut: <code>https://paste.mjollnir.fr/r/<?= $key ?></code></p>
<p>Ce paste expire dans: <?= $redis->ttl($key) >= 0 ? gmdate("H\Hi:s", $redis->ttl($key)) : 'longtemps' ?></p>
<p><a href="/">[ Accueil ]</a></p>
<!-- TODO ajouter differents modes pour render: source code, raw -->
</body>
</html>
