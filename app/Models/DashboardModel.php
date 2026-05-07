<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class DashboardModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function summary(string $periodStart, string $periodEnd): array
    {
        $families = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_families,
                COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END), 0) AS active_families,
                COALESCE(SUM(CASE WHEN needs_visit = 1 THEN 1 ELSE 0 END), 0) AS families_needing_visit
             FROM families'
        );

        $people = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_people,
                COALESCE(SUM(CASE WHEN is_homeless = 1 THEN 1 ELSE 0 END), 0) AS homeless_people
             FROM people'
        );

        $children = $this->fetchOne(
            'SELECT COUNT(*) AS total_children FROM children'
        );

        $deliveries = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_delivery_records,
                COALESCE(SUM(CASE WHEN d.status = \'retirou\' THEN d.quantity ELSE 0 END), 0) AS withdrawn_baskets
             FROM deliveries d
             INNER JOIN delivery_events de ON de.id = d.event_id
             WHERE DATE(de.event_date) BETWEEN :period_start AND :period_end',
            [
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ]
        );

        $referrals = $this->fetchOne(
            'SELECT COUNT(*) AS total_referrals
             FROM referrals
             WHERE DATE(referral_date) BETWEEN :period_start AND :period_end',
            [
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ]
        );

        $equipment = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_equipment,
                COALESCE(SUM(CASE WHEN status = \'disponivel\' THEN 1 ELSE 0 END), 0) AS available_equipment,
                COALESCE(SUM(CASE WHEN status = \'emprestado\' THEN 1 ELSE 0 END), 0) AS loaned_equipment,
                COALESCE(SUM(CASE WHEN status = \'manutencao\' THEN 1 ELSE 0 END), 0) AS maintenance_equipment,
                COALESCE(SUM(CASE WHEN status = \'inativo\' THEN 1 ELSE 0 END), 0) AS inactive_equipment
             FROM equipment'
        );

        return [
            'families' => $families,
            'people' => $people,
            'children' => $children,
            'deliveries' => $deliveries,
            'referrals' => $referrals,
            'equipment' => $equipment,
        ];
    }

    public function countStaleFamilies(int $days): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total
             FROM families
             WHERE updated_at IS NOT NULL
               AND updated_at < DATE_SUB(NOW(), INTERVAL :days DAY)'
        );
        $stmt->bindValue(':days', max(1, $days), PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function listStaleFamilies(int $days, int $limit = 10): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, responsible_name, neighborhood, city, updated_at
             FROM families
             WHERE updated_at IS NOT NULL
               AND updated_at < DATE_SUB(NOW(), INTERVAL :days DAY)
             ORDER BY updated_at ASC, id ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':days', max(1, $days), PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function listNeighborhoodPriorityMap(int $limit = 30, int $staleDays = 30): array
    {
        $sql = 'SELECT
                    base.neighborhood_label AS neighborhood,
                    base.total_families,
                    COALESCE(served.served_families, 0) AS served_families,
                    GREATEST(base.total_families - COALESCE(served.served_families, 0), 0) AS unserved_families,
                    ROUND(
                        (GREATEST(base.total_families - COALESCE(served.served_families, 0), 0) / NULLIF(base.total_families, 0)) * 100,
                        2
                    ) AS unserved_percentage,
                    COALESCE(visits.pending_visits, 0) AS pending_visits,
                    COALESCE(docs.pending_documents, 0) AS pending_documents,
                    COALESCE(stale.stale_families, 0) AS stale_families,
                    COALESCE(homeless.homeless_people, 0) AS homeless_people,
                    COALESCE(children.children_count, 0) AS children_count,
                    (
                        GREATEST(base.total_families - COALESCE(served.served_families, 0), 0) * 3
                        + COALESCE(visits.pending_visits, 0) * 2
                        + COALESCE(docs.pending_documents, 0) * 2
                        + COALESCE(stale.stale_families, 0)
                        + COALESCE(homeless.homeless_people, 0) * 3
                        + COALESCE(children.children_count, 0)
                    ) AS priority_score
                FROM (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        MAX(TRIM(f.neighborhood)) AS neighborhood_label,
                        COUNT(*) AS total_families
                    FROM families f
                    WHERE f.is_active = 1
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) base
                LEFT JOIN (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        COUNT(DISTINCT d.family_id) AS served_families
                    FROM deliveries d
                    INNER JOIN families f ON f.id = d.family_id
                    WHERE d.status = \'retirou\'
                      AND f.is_active = 1
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) served ON served.neighborhood_key = base.neighborhood_key
                LEFT JOIN (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        COUNT(DISTINCT v.id) AS pending_visits
                    FROM visits v
                    INNER JOIN families f ON f.id = v.family_id
                    WHERE v.status = \'pendente\'
                      AND f.is_active = 1
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) visits ON visits.neighborhood_key = base.neighborhood_key
                LEFT JOIN (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        COUNT(*) AS pending_documents
                    FROM families f
                    WHERE f.is_active = 1
                      AND f.documentation_status <> \'ok\'
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) docs ON docs.neighborhood_key = base.neighborhood_key
                LEFT JOIN (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        COUNT(*) AS stale_families
                    FROM families f
                    WHERE f.is_active = 1
                      AND f.updated_at IS NOT NULL
                      AND f.updated_at < DATE_SUB(NOW(), INTERVAL :stale_days DAY)
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) stale ON stale.neighborhood_key = base.neighborhood_key
                LEFT JOIN (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        COUNT(DISTINCT p.id) AS homeless_people
                    FROM social_records sr
                    INNER JOIN people p ON p.id = sr.person_id
                    INNER JOIN families f ON f.id = sr.family_id
                    WHERE p.is_homeless = 1
                      AND f.is_active = 1
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) homeless ON homeless.neighborhood_key = base.neighborhood_key
                LEFT JOIN (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        COUNT(DISTINCT c.id) AS children_count
                    FROM children c
                    INNER JOIN families f ON f.id = c.family_id
                    WHERE f.is_active = 1
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) children ON children.neighborhood_key = base.neighborhood_key
                ORDER BY priority_score DESC,
                         unserved_families DESC,
                         base.total_families DESC,
                         base.neighborhood_label ASC
                LIMIT :limit_rows';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':stale_days', max(1, $staleDays), PDO::PARAM_INT);
        $stmt->bindValue(':limit_rows', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    public function listNeighborhoodNeedHeatmap(int $limit = 30): array
    {
        $sql = 'SELECT
                    base.neighborhood_label AS neighborhood,
                    base.total_families,
                    COALESCE(served.served_families, 0) AS served_families,
                    ROUND(
                        (COALESCE(served.served_families, 0) / NULLIF(base.total_families, 0)) * 100,
                        2
                    ) AS need_percentage
                FROM (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        MAX(TRIM(f.neighborhood)) AS neighborhood_label,
                        COUNT(*) AS total_families
                    FROM families f
                    WHERE f.is_active = 1
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) base
                LEFT JOIN (
                    SELECT
                        UPPER(TRIM(f.neighborhood)) AS neighborhood_key,
                        COUNT(DISTINCT d.family_id) AS served_families
                    FROM deliveries d
                    INNER JOIN families f ON f.id = d.family_id
                    WHERE d.status = \'retirou\'
                      AND f.is_active = 1
                      AND TRIM(COALESCE(f.neighborhood, \'\')) <> \'\'
                      AND LOWER(TRIM(COALESCE(f.city, \'\'))) IN (\'taubate\', \'taubaté\')
                    GROUP BY UPPER(TRIM(f.neighborhood))
                ) served ON served.neighborhood_key = base.neighborhood_key
                ORDER BY need_percentage DESC, base.total_families DESC, base.neighborhood_label ASC
                LIMIT :limit_rows';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit_rows', max(1, min(100, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    private function fetchOne(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return is_array($row) ? $row : [];
    }
}
