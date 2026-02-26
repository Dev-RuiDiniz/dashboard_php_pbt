<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    public static function connect(array $config): PDO
    {
        $driver = $config['connection'] ?? 'mysql';

        if ($driver !== 'mysql') {
            throw new RuntimeException('Apenas conexao mysql esta suportada no momento.');
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = (int) ($config['port'] ?? 3306);
        $dbname = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $dbname,
            $charset
        );

        try {
            return new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Falha ao conectar ao banco de dados.', 0, $exception);
        }
    }
}

