<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class DeliveryModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listByEventId(int $eventId): array
    {
        return $this->listByEventIdFiltered($eventId, []);
    }

    public function listByEventIdFiltered(int $eventId, array $filters): array
    {
        $sql = 'SELECT
                d.*,
                f.responsible_name AS family_name,
                p.full_name AS person_full_name,
                p.social_name AS person_social_name,
                u.name AS delivered_by_name
             FROM deliveries d
             LEFT JOIN families f ON f.id = d.family_id
             LEFT JOIN people p ON p.id = d.person_id
             LEFT JOIN users u ON u.id = d.delivered_by
             WHERE d.event_id = :event_id';
        $params = ['event_id' => $eventId];

        $status = trim((string) ($filters['status'] ?? ''));
        if (in_array($status, ['nao_veio', 'presente', 'retirou'], true)) {
            $sql .= ' AND d.status = :status';
            $params['status'] = $status;
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $sql .= ' AND (
                f.responsible_name LIKE :q
                OR p.full_name LIKE :q
                OR p.social_name LIKE :q
                OR d.document_id LIKE :q
                OR CAST(d.ticket_number AS CHAR) LIKE :q
            )';
            $params['q'] = '%' . $q . '%';
        }

        $sql .= ' ORDER BY d.ticket_number ASC, d.id ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM deliveries WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function findFamilyCandidates(array $criteria): array
    {
        $sql = 'SELECT
                    f.id,
                    f.responsible_name,
                    f.cpf_responsible,
                    f.rg_responsible,
                    f.city,
                    f.neighborhood,
                    f.documentation_status,
                    f.needs_visit,
                    f.family_income_total,
                    COALESCE(COUNT(c.id), 0) AS registered_children
                FROM families f
                LEFT JOIN children c ON c.family_id = f.id
                WHERE f.is_active = 1';
        $params = [];

        $city = trim((string) ($criteria['city'] ?? ''));
        if ($city !== '') {
            $sql .= ' AND f.city = :city';
            $params['city'] = $city;
        }

        $neighborhood = trim((string) ($criteria['neighborhood'] ?? ''));
        if ($neighborhood !== '') {
            $sql .= ' AND f.neighborhood LIKE :neighborhood';
            $params['neighborhood'] = '%' . $neighborhood . '%';
        }

        if ((int) ($criteria['only_pending_documentation'] ?? 0) === 1) {
            $sql .= ' AND f.documentation_status IN (\'pendente\', \'parcial\')';
        }

        if ((int) ($criteria['only_needs_visit'] ?? 0) === 1) {
            $sql .= ' AND f.needs_visit = 1';
        }

        if ((int) ($criteria['only_with_children'] ?? 0) === 1) {
            $sql .= ' AND f.children_count > 0';
        }

        $maxIncome = trim((string) ($criteria['max_income'] ?? ''));
        if ($maxIncome !== '') {
            $sql .= ' AND f.family_income_total <= :max_income';
            $params['max_income'] = number_format((float) $maxIncome, 2, '.', '');
        }

        $sql .= ' GROUP BY
                    f.id,
                    f.responsible_name,
                    f.cpf_responsible,
                    f.rg_responsible,
                    f.city,
                    f.neighborhood,
                    f.documentation_status,
                    f.needs_visit,
                    f.family_income_total
                  ORDER BY
                    f.neighborhood ASC,
                    f.responsible_name ASC
                  LIMIT :limit_rows';

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit_rows', max(1, min(500, (int) ($criteria['limit'] ?? 30))), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function existsFamilyInEvent(int $eventId, int $familyId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM deliveries WHERE event_id = :event_id AND family_id = :family_id LIMIT 1'
        );
        $stmt->execute([
            'event_id' => $eventId,
            'family_id' => $familyId,
        ]);
        return $stmt->fetchColumn() !== false;
    }

    public function existsPersonInEvent(int $eventId, int $personId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM deliveries WHERE event_id = :event_id AND person_id = :person_id LIMIT 1'
        );
        $stmt->execute([
            'event_id' => $eventId,
            'person_id' => $personId,
        ]);
        return $stmt->fetchColumn() !== false;
    }

    public function nextTicketNumber(int $eventId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(MAX(ticket_number), 0) + 1 AS next_ticket FROM deliveries WHERE event_id = :event_id');
        $stmt->execute(['event_id' => $eventId]);
        $row = $stmt->fetch();
        return max(1, (int) ($row['next_ticket'] ?? 1));
    }

    public function totalQuantityByEvent(int $eventId): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(quantity), 0) AS total_qty FROM deliveries WHERE event_id = :event_id');
        $stmt->execute(['event_id' => $eventId]);
        $row = $stmt->fetch();
        return (int) ($row['total_qty'] ?? 0);
    }

    public function summaryByEventId(int $eventId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(quantity), 0) AS total_quantity,
                COALESCE(SUM(CASE WHEN status = \'nao_veio\' THEN 1 ELSE 0 END), 0) AS status_nao_veio,
                COALESCE(SUM(CASE WHEN status = \'presente\' THEN 1 ELSE 0 END), 0) AS status_presente,
                COALESCE(SUM(CASE WHEN status = \'retirou\' THEN 1 ELSE 0 END), 0) AS status_retirou,
                COALESCE(SUM(CASE WHEN status = \'retirou\' THEN quantity ELSE 0 END), 0) AS withdrawn_quantity
             FROM deliveries
             WHERE event_id = :event_id'
        );
        $stmt->execute(['event_id' => $eventId]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : [];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO deliveries (
                event_id, family_id, person_id, ticket_number, document_id, observations,
                status, quantity, delivered_at, delivered_by, signature_name
             ) VALUES (
                :event_id, :family_id, :person_id, :ticket_number, :document_id, :observations,
                :status, :quantity, :delivered_at, :delivered_by, :signature_name
             )'
        );
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateStatus(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE deliveries
             SET status = :status,
                 delivered_at = :delivered_at,
                 delivered_by = :delivered_by,
                 signature_name = :signature_name
             WHERE id = :id'
        );
        $stmt->execute($data);
    }

    public function existsFamilyDeliveryInMonth(int $familyId, string $eventDate, ?int $excludeEventId = null): bool
    {
        $sql = 'SELECT 1
                FROM deliveries d
                INNER JOIN delivery_events de ON de.id = d.event_id
                WHERE d.family_id = :family_id
                  AND YEAR(de.event_date) = YEAR(:event_date)
                  AND MONTH(de.event_date) = MONTH(:event_date)
                  AND d.status = :status
                LIMIT 1';
        $params = [
            'family_id' => $familyId,
            'event_date' => $eventDate,
            'status' => 'retirou',
        ];

        if ($excludeEventId !== null) {
            $sql = str_replace('LIMIT 1', 'AND d.event_id <> :exclude_event_id LIMIT 1', $sql);
            $params['exclude_event_id'] = $excludeEventId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }

    public function existsPersonDeliveryInMonth(int $personId, string $eventDate, ?int $excludeEventId = null): bool
    {
        $sql = 'SELECT 1
                FROM deliveries d
                INNER JOIN delivery_events de ON de.id = d.event_id
                WHERE d.person_id = :person_id
                  AND YEAR(de.event_date) = YEAR(:event_date)
                  AND MONTH(de.event_date) = MONTH(:event_date)
                  AND d.status = :status
                LIMIT 1';
        $params = [
            'person_id' => $personId,
            'event_date' => $eventDate,
            'status' => 'retirou',
        ];

        if ($excludeEventId !== null) {
            $sql = str_replace('LIMIT 1', 'AND d.event_id <> :exclude_event_id LIMIT 1', $sql);
            $params['exclude_event_id'] = $excludeEventId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() !== false;
    }
}
