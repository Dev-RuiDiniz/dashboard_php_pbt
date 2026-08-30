<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;
use App\Models\FamilyModel;

$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';

Env::load($basePath);
$pdo = Database::connect(require $basePath . '/config/database.php');
$model = new FamilyModel($pdo);
$prefix = 'Zeta Correcao ' . bin2hex(random_bytes(4));

$pdo->beginTransaction();
try {
    $insert = $pdo->prepare(
        'INSERT INTO families (responsible_name, city, state, is_active)
         VALUES (:responsible_name, :city, :state, 1)'
    );

    for ($index = 1; $index <= 205; $index++) {
        $insert->execute([
            'responsible_name' => sprintf('%s %03d', $prefix, $index),
            'city' => 'Sao Paulo',
            'state' => 'SP',
        ]);
    }

    $rows = $model->search([]);
    $names = array_map(static fn (array $row): string => (string) ($row['responsible_name'] ?? ''), $rows);
    $lastName = sprintf('%s %03d', $prefix, 205);

    if (count($rows) < 205 || !in_array($lastName, $names, true)) {
        throw new RuntimeException('A listagem nao retornou todas as familias cadastradas.');
    }

    echo 'LISTAGEM_FAMILIAS_COMPLETA: OK' . PHP_EOL;
} finally {
    $pdo->rollBack();
}
