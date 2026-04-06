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
        $timezone = trim((string) ($config['timezone'] ?? 'America/Sao_Paulo'));

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $dbname,
            $charset
        );

        try {
            $pdo = new PDO(
                $dsn,
                $config['username'] ?? '',
                $config['password'] ?? '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            self::configureTimezone($pdo, $timezone);

            return $pdo;
        } catch (PDOException $exception) {
            throw new RuntimeException('Falha ao conectar ao banco de dados.', 0, $exception);
        }
    }

    private static function configureTimezone(PDO $pdo, string $timezone): void
    {
        $offset = self::resolveUtcOffset($timezone);
        if ($offset === null) {
            return;
        }

        $stmt = $pdo->prepare('SET time_zone = :time_zone');
        $stmt->execute(['time_zone' => $offset]);
    }

    private static function resolveUtcOffset(string $timezone): ?string
    {
        try {
            $tz = new \DateTimeZone($timezone);
            $now = new \DateTimeImmutable('now', $tz);
        } catch (\Throwable) {
            return null;
        }

        $offsetSeconds = $tz->getOffset($now);
        $sign = $offsetSeconds >= 0 ? '+' : '-';
        $offsetSeconds = abs($offsetSeconds);
        $hours = intdiv($offsetSeconds, 3600);
        $minutes = intdiv($offsetSeconds % 3600, 60);

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }
}

