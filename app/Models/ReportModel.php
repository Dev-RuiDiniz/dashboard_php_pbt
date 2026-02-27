<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class ReportModel
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function monthlyFamilies(array $filters): array
    {
        $where = ' WHERE DATE(f.created_at) BETWEEN :period_start AND :period_end';
        $params = [
            'period_start' => (string) $filters['period_start'],
            'period_end' => (string) $filters['period_end'],
        ];

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'ativo' || $status === 'inativo') {
            $where .= ' AND f.is_active = :is_active';
            $params['is_active'] = $status === 'ativo' ? 1 : 0;
        }

        $neighborhood = trim((string) ($filters['neighborhood'] ?? ''));
        if ($neighborhood !== '') {
            $where .= ' AND f.neighborhood = :neighborhood';
            $params['neighborhood'] = $neighborhood;
        }

        $summaryStmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total_families,
                COALESCE(SUM(CASE WHEN f.is_active = 1 THEN 1 ELSE 0 END), 0) AS active_families,
                COALESCE(SUM(CASE WHEN f.is_active = 0 THEN 1 ELSE 0 END), 0) AS inactive_families
             FROM families f' . $where
        );
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch();

        $listStmt = $this->pdo->prepare(
            'SELECT
                f.id, f.responsible_name, f.phone, f.neighborhood, f.city, f.documentation_status,
                f.is_active, f.created_at
             FROM families f' . $where . '
             ORDER BY f.created_at DESC, f.id DESC
             LIMIT 300'
        );
        $listStmt->execute($params);
        $list = $listStmt->fetchAll();

        return [
            'summary' => is_array($summary) ? $summary : [],
            'items' => is_array($list) ? $list : [],
        ];
    }

    public function monthlyBaskets(array $filters): array
    {
        $where = ' WHERE DATE(de.event_date) BETWEEN :period_start AND :period_end';
        $params = [
            'period_start' => (string) $filters['period_start'],
            'period_end' => (string) $filters['period_end'],
        ];

        $status = trim((string) ($filters['status'] ?? ''));
        if (in_array($status, ['nao_veio', 'presente', 'retirou'], true)) {
            $where .= ' AND d.status = :delivery_status';
            $params['delivery_status'] = $status;
        }

        $neighborhood = trim((string) ($filters['neighborhood'] ?? ''));
        if ($neighborhood !== '') {
            $where .= ' AND (f.neighborhood = :neighborhood)';
            $params['neighborhood'] = $neighborhood;
        }

        $summaryStmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(d.quantity), 0) AS total_baskets,
                COALESCE(SUM(CASE WHEN d.status = \'retirou\' THEN d.quantity ELSE 0 END), 0) AS withdrawn_baskets
             FROM deliveries d
             INNER JOIN delivery_events de ON de.id = d.event_id
             LEFT JOIN families f ON f.id = d.family_id' . $where
        );
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch();

        $listStmt = $this->pdo->prepare(
            'SELECT
                d.id, d.event_id, d.family_id, d.person_id, d.status, d.quantity, d.delivered_at,
                de.name AS event_name, de.event_date,
                f.responsible_name AS family_name, f.neighborhood
             FROM deliveries d
             INNER JOIN delivery_events de ON de.id = d.event_id
             LEFT JOIN families f ON f.id = d.family_id' . $where . '
             ORDER BY de.event_date DESC, d.id DESC
             LIMIT 500'
        );
        $listStmt->execute($params);
        $list = $listStmt->fetchAll();

        return [
            'summary' => is_array($summary) ? $summary : [],
            'items' => is_array($list) ? $list : [],
        ];
    }

    public function monthlyChildren(array $filters): array
    {
        $where = ' WHERE DATE(c.created_at) BETWEEN :period_start AND :period_end';
        $params = [
            'period_start' => (string) $filters['period_start'],
            'period_end' => (string) $filters['period_end'],
        ];

        $neighborhood = trim((string) ($filters['neighborhood'] ?? ''));
        if ($neighborhood !== '') {
            $where .= ' AND f.neighborhood = :neighborhood';
            $params['neighborhood'] = $neighborhood;
        }

        $summaryStmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total_children
             FROM children c
             INNER JOIN families f ON f.id = c.family_id' . $where
        );
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch();

        $listStmt = $this->pdo->prepare(
            'SELECT
                c.id, c.name, c.birth_date, c.age_years, c.relationship, c.created_at,
                f.responsible_name AS family_name, f.neighborhood
             FROM children c
             INNER JOIN families f ON f.id = c.family_id' . $where . '
             ORDER BY c.created_at DESC, c.id DESC
             LIMIT 300'
        );
        $listStmt->execute($params);
        $list = $listStmt->fetchAll();

        return [
            'summary' => is_array($summary) ? $summary : [],
            'items' => is_array($list) ? $list : [],
        ];
    }

    public function monthlyReferrals(array $filters): array
    {
        $where = ' WHERE DATE(r.referral_date) BETWEEN :period_start AND :period_end';
        $params = [
            'period_start' => (string) $filters['period_start'],
            'period_end' => (string) $filters['period_end'],
        ];

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '' && !in_array($status, ['ativo', 'inativo', 'nao_veio', 'presente', 'retirou'], true)) {
            $where .= ' AND r.status = :referral_status';
            $params['referral_status'] = $status;
        }

        $neighborhood = trim((string) ($filters['neighborhood'] ?? ''));
        if ($neighborhood !== '') {
            $where .= ' AND f.neighborhood = :neighborhood';
            $params['neighborhood'] = $neighborhood;
        }

        $summaryStmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total_referrals,
                COALESCE(SUM(CASE WHEN r.status IN (\'concluido\', \'finalizado\') THEN 1 ELSE 0 END), 0) AS completed_referrals
             FROM referrals r
             INNER JOIN social_records sr ON sr.id = r.social_record_id
             LEFT JOIN families f ON f.id = sr.family_id' . $where
        );
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch();

        $listStmt = $this->pdo->prepare(
            'SELECT
                r.id, r.referral_type, r.referral_date, r.status, r.notes,
                f.responsible_name AS family_name, f.neighborhood,
                p.full_name AS person_name
             FROM referrals r
             INNER JOIN social_records sr ON sr.id = r.social_record_id
             INNER JOIN people p ON p.id = sr.person_id
             LEFT JOIN families f ON f.id = sr.family_id' . $where . '
             ORDER BY r.referral_date DESC, r.id DESC
             LIMIT 400'
        );
        $listStmt->execute($params);
        $list = $listStmt->fetchAll();

        return [
            'summary' => is_array($summary) ? $summary : [],
            'items' => is_array($list) ? $list : [],
        ];
    }
}

