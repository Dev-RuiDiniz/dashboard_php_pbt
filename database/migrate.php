<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;

require dirname(__DIR__) . '/vendor/autoload.php';

$basePath = dirname(__DIR__);
Env::load($basePath);

$pdo = Database::connect(require $basePath . '/config/database.php');

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
);

$applied = $pdo->query('SELECT migration FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
$appliedSet = [];
foreach ($applied ?: [] as $migration) {
    $appliedSet[(string) $migration] = true;
}

$migrationDir = __DIR__ . '/migrations';
$files = glob($migrationDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    $name = basename($file);

    if (isset($appliedSet[$name])) {
        echo "[skip] {$name}" . PHP_EOL;
        continue;
    }

    // Wrappers 001/002 direcionam para os scripts principais da sprint.
    if (str_contains($name, 'schema')) {
        $sql = file_get_contents(__DIR__ . '/schema.sql');
    } elseif (str_contains($name, 'seed')) {
        $sql = file_get_contents(__DIR__ . '/seeds.sql');
    } else {
        $sql = file_get_contents($file);
    }

    if ($sql === false) {
        throw new RuntimeException("Falha ao ler migracao: {$name}");
    }

    echo "[run ] {$name}" . PHP_EOL;
    $pdo->exec($sql);

    $stmt = $pdo->prepare('INSERT INTO schema_migrations (migration) VALUES (:migration)');
    $stmt->execute(['migration' => $name]);
}

echo 'Migrations finalizadas.' . PHP_EOL;

