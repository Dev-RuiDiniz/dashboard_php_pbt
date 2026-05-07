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
