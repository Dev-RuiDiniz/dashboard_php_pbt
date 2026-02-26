<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class AuditLogModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent, details_json)
             VALUES (:user_id, :action, :entity, :entity_id, :ip_address, :user_agent, :details_json)'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }
}

