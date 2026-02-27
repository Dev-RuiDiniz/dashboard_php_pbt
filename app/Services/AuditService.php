<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLogModel;
use PDO;
use Throwable;

final class AuditService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function log(
        string $action,
        string $entity,
        ?int $entityId,
        ?int $userId = null,
        array $details = []
    ): void {
        try {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null;
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : null;
            $encodedDetails = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            (new AuditLogModel($this->pdo))->create([
                'user_id' => $userId,
                'action' => $action,
                'entity' => $entity,
                'entity_id' => $entityId,
                'ip_address' => $ip !== '' ? $ip : null,
                'user_agent' => $userAgent !== '' ? $userAgent : null,
                'details_json' => $encodedDetails !== false ? $encodedDetails : null,
            ]);
        } catch (Throwable $exception) {
            // Auditoria nao deve interromper o fluxo principal.
        }
    }
}

