<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class EquipmentModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function search(array $filters): array
    {
        $sql = 'SELECT id, code, type, condition_state, status, notes, created_at, updated_at
                FROM equipment
                WHERE 1=1';
        $params = [];

        $code = trim((string) ($filters['code'] ?? ''));
        if ($code !== '') {
            $sql .= ' AND code LIKE :code';
            $params['code'] = '%' . $code . '%';
        }

        $type = trim((string) ($filters['type'] ?? ''));
        if ($type !== '') {
            $sql .= ' AND type = :type';
            $params['type'] = $type;
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $sql .= ' AND status = :status';
            $params['status'] = $status;
        }

        $sql .= ' ORDER BY id DESC LIMIT 500';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM equipment WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO equipment (code, type, condition_state, status, notes)
             VALUES (:code, :type, :condition_state, :status, :notes)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE equipment
             SET type = :type,
                 condition_state = :condition_state,
                 status = :status,
                 notes = :notes
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM equipment WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function listTypes(): array
    {
        $stmt = $this->pdo->query('SELECT DISTINCT type FROM equipment WHERE type IS NOT NULL AND type <> \'\' ORDER BY type ASC');
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return is_array($rows) ? array_values(array_filter(array_map('strval', $rows))) : [];
    }

    public function existsCode(string $code, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM equipment WHERE code = :code';
        $params = ['code' => $code];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    public function nextCodeByType(string $type): string
    {
        $prefix = $this->buildPrefix($type);
        $stmt = $this->pdo->prepare('SELECT code FROM equipment WHERE code LIKE :prefix ORDER BY id DESC LIMIT 500');
        $stmt->execute(['prefix' => $prefix . '-%']);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $max = 0;
        if (is_array($rows)) {
            foreach ($rows as $code) {
                $codeValue = (string) $code;
                if (preg_match('/^' . preg_quote($prefix, '/') . '-(\d{4})$/', $codeValue, $matches) === 1) {
                    $num = (int) ($matches[1] ?? 0);
                    if ($num > $max) {
                        $max = $num;
                    }
                }
            }
        }
        return sprintf('%s-%04d', $prefix, $max + 1);
    }

    private function buildPrefix(string $type): string
    {
        $clean = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $type) ?: $type));
        if ($clean === '') {
            return 'EQP';
        }
        if (strlen($clean) >= 3) {
            return substr($clean, 0, 3);
        }
        return str_pad($clean, 3, 'X');
    }
}

