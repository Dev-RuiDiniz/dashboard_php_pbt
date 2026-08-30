<?php

declare(strict_types=1);

use App\Core\Database;
use App\Core\Env;
use App\Models\FamilyModel;

$basePath = dirname(__DIR__);
require $basePath . '/vendor/autoload.php';

Env::load($basePath);

$css = (string) file_get_contents($basePath . '/public/assets/app.css');

$assert = static function (bool $condition, string $message): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};

$assert(
    preg_match('/\.table-responsive\s*\{[^}]*max-height:\s*60rem;[^}]*overflow-x:\s*auto;[^}]*overflow-y:\s*auto;/s', $css) === 1,
    'O container de tabelas nao possui limite vertical e rolagem nos dois eixos.'
);
$assert(
    preg_match('/\.list-group\s*\{[^}]*max-height:\s*60rem;[^}]*overflow-y:\s*auto;/s', $css) === 1,
    'O container de listas nao possui limite vertical.'
);
$assert(
    preg_match('/\.table-responsive\s*>\s*\.table\s*>\s*thead\s*>\s*tr\s*>\s*th\s*\{[^}]*position:\s*sticky;/s', $css) === 1,
    'O cabecalho das tabelas nao esta fixo durante a rolagem.'
);

$pdo = Database::connect(require $basePath . '/config/database.php');
$model = new FamilyModel($pdo);
$prefix = 'Scroll Limite ' . bin2hex(random_bytes(4));

$pdo->beginTransaction();
try {
    $insert = $pdo->prepare(
        'INSERT INTO families (responsible_name, city, state, is_active)
         VALUES (:responsible_name, :city, :state, 1)'
    );

    for ($index = 1; $index <= 21; $index++) {
        $insert->execute([
            'responsible_name' => sprintf('%s %02d', $prefix, $index),
            'city' => 'Sao Paulo',
            'state' => 'SP',
        ]);
    }

    $rows = $model->search(['q' => $prefix]);
    $names = array_map(static fn (array $row): string => (string) ($row['responsible_name'] ?? ''), $rows);
    $lastName = sprintf('%s 21', $prefix);

    $assert(count($rows) === 21, 'A busca de familias nao retornou os 21 fixtures temporarios.');
    $assert(in_array($lastName, $names, true), 'O ultimo nome dos fixtures nao foi retornado.');

    echo 'SCROLL_LIST_LIMIT_20: OK' . PHP_EOL;
} finally {
    $pdo->rollBack();
}
