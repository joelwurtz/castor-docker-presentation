<?php

function check(string $label, bool $ok, string $detail = ''): string
{
    return sprintf(
        '<li class="%s"><span>%s</span> %s <small>%s</small></li>',
        $ok ? 'ok' : 'ko',
        $ok ? '✔' : '✘',
        htmlspecialchars($label),
        htmlspecialchars($detail),
    );
}

$checks = [];

$checks[] = check('PHP', true, PHP_VERSION . ' — ' . PHP_SAPI);
$checks[] = check('memory_limit', '1G' === ini_get('memory_limit'), ini_get('memory_limit') . ' (② withPhpIni)');

$databaseUrl = getenv('DATABASE_URL') ?: '';
try {
    $url = parse_url($databaseUrl);
    $pdo = new PDO(
        sprintf('pgsql:host=%s;port=%d;dbname=%s', $url['host'], $url['port'] ?? 5432, ltrim($url['path'], '/')),
        $url['user'],
        $url['pass'],
    );
    $checks[] = check('Postgres', true, $pdo->query('SELECT version()')->fetchColumn());
} catch (Throwable $e) {
    $checks[] = check('Postgres', false, $databaseUrl ? $e->getMessage() : 'DATABASE_URL absent (② link)');
}

if (extension_loaded('redis')) {
    try {
        $redis = new Redis();
        $redis->connect('redis');
        $checks[] = check('Redis', true, 'ext-redis + PONG: ' . var_export($redis->ping(), true));
    } catch (Throwable $e) {
        $checks[] = check('Redis', false, 'ext-redis chargée, serveur injoignable: ' . $e->getMessage());
    }
} else {
    $checks[] = check('Redis', false, 'ext-redis absente (② addExtension)');
}

$banner = is_executable('/usr/bin/figlet') ? shell_exec('/usr/bin/figlet -f slant "Castor Docker"') : null;
$checks[] = check('figlet dans l\'image', null !== $banner, $banner ? '③ Dockerfile Twig' : 'absent (③ Dockerfile Twig)');

$message = getenv('DEMO_MESSAGE');
$checks[] = check('DEMO_MESSAGE', false !== $message, $message ?: 'absent (⑥ compose.override.yaml)');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Castor Docker — démo</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #e2e8f0; max-width: 900px; margin: 3rem auto; padding: 0 1rem; }
        h1 { color: #f59e0b; }
        pre { color: #38bdf8; font-size: .9rem; }
        ul { list-style: none; padding: 0; }
        li { padding: .6rem 1rem; margin: .4rem 0; border-radius: .4rem; background: #1e293b; font-size: 1.2rem; }
        li span { display: inline-block; width: 1.5rem; }
        li.ok span { color: #22c55e; }
        li.ko span { color: #ef4444; }
        small { color: #94a3b8; margin-left: .5rem; }
    </style>
</head>
<body>
    <?php if ($banner): ?><pre><?= htmlspecialchars($banner) ?></pre><?php else: ?><h1>Castor Docker</h1><?php endif ?>
    <ul><?= implode("\n", $checks) ?></ul>
    <p><small>Servi par <?= htmlspecialchars(gethostname()) ?> — projet <?= htmlspecialchars(getenv('PROJECT_NAME') ?: '?') ?></small></p>
</body>
</html>
