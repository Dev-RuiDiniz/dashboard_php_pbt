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

    public function monthlyEquipment(array $filters): array
    {
        $where = ' WHERE DATE(el.loan_date) BETWEEN :period_start AND :period_end';
        $params = [
            'period_start' => (string) $filters['period_start'],
            'period_end' => (string) $filters['period_end'],
        ];

        $neighborhood = trim((string) ($filters['neighborhood'] ?? ''));
        if ($neighborhood !== '') {
            $where .= ' AND (f.neighborhood = :neighborhood)';
            $params['neighborhood'] = $neighborhood;
        }

        $summaryStmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total_loans,
                COALESCE(SUM(CASE WHEN el.return_date IS NULL THEN 1 ELSE 0 END), 0) AS open_loans,
                COALESCE(SUM(CASE WHEN el.return_date IS NOT NULL THEN 1 ELSE 0 END), 0) AS returned_loans,
                COALESCE(SUM(CASE WHEN el.return_date IS NULL AND el.due_date < CURDATE() THEN 1 ELSE 0 END), 0) AS overdue_loans
             FROM equipment_loans el
             LEFT JOIN families f ON f.id = el.family_id' . $where
        );
        $summaryStmt->execute($params);
        $summary = $summaryStmt->fetch();

        $maintenanceStmt = $this->pdo->query(
            'SELECT COUNT(*) AS maintenance_equipment
             FROM equipment
             WHERE status = \'manutencao\''
        );
        $maintenance = $maintenanceStmt->fetch();
        if (is_array($summary)) {
            $summary['maintenance_equipment'] = (int) ($maintenance['maintenance_equipment'] ?? 0);
        }

        $listStmt = $this->pdo->prepare(
            'SELECT
                el.id, el.loan_date, el.due_date, el.return_date, el.return_condition,
                e.code AS equipment_code, e.type AS equipment_type,
                f.responsible_name AS family_name, f.neighborhood,
                p.full_name AS person_name
             FROM equipment_loans el
             INNER JOIN equipment e ON e.id = el.equipment_id
             LEFT JOIN families f ON f.id = el.family_id
             LEFT JOIN people p ON p.id = el.person_id' . $where . '
             ORDER BY el.loan_date DESC, el.id DESC
             LIMIT 400'
        );
        $listStmt->execute($params);
        $list = $listStmt->fetchAll();

        return [
            'summary' => is_array($summary) ? $summary : [],
            'items' => is_array($list) ? $list : [],
        ];
    }

    public function monthlyPendencies(array $filters, int $staleDays = 30): array
    {
        $summary = [
            'pending_documentation' => 0,
            'pending_visits' => 0,
            'overdue_returns' => 0,
            'stale_updates' => 0,
        ];

        $neighborhood = trim((string) ($filters['neighborhood'] ?? ''));
        $summaryWhere = '';
        $summaryParams = [];
        if ($neighborhood !== '') {
            $summaryWhere = ' AND neighborhood = :neighborhood';
            $summaryParams['neighborhood'] = $neighborhood;
        }

        $docSummaryStmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total
             FROM families
             WHERE documentation_status IN (\'pendente\', \'parcial\')' . $summaryWhere
        );
        $docSummaryStmt->execute($summaryParams);
        $docSummary = $docSummaryStmt->fetch();
        $summary['pending_documentation'] = (int) ($docSummary['total'] ?? 0);

        $visitSummaryStmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total
             FROM visits v
             LEFT JOIN families f ON f.id = v.family_id
             WHERE v.status IN (\'pendente\', \'agendada\')' .
            ($neighborhood !== '' ? ' AND f.neighborhood = :neighborhood' : '')
        );
        $visitSummaryStmt->execute($summaryParams);
        $visitSummary = $visitSummaryStmt->fetch();
        $summary['pending_visits'] = (int) ($visitSummary['total'] ?? 0);

        $overdueSummaryStmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS total
             FROM equipment_loans el
             LEFT JOIN families f ON f.id = el.family_id
             WHERE el.return_date IS NULL
               AND el.due_date < CURDATE()' .
            ($neighborhood !== '' ? ' AND f.neighborhood = :neighborhood' : '')
        );
        $overdueSummaryStmt->execute($summaryParams);
        $overdueSummary = $overdueSummaryStmt->fetch();
        $summary['overdue_returns'] = (int) ($overdueSummary['total'] ?? 0);

        $staleDays = max(1, $staleDays);
        $staleSummaryStmt = $this->pdo->prepare(
            'SELECT
                (
                    SELECT COUNT(*) FROM families
                    WHERE updated_at IS NOT NULL
                      AND updated_at < DATE_SUB(NOW(), INTERVAL :days_f DAY)
                ) +
                (
                    SELECT COUNT(*) FROM people
                    WHERE updated_at IS NOT NULL
                      AND updated_at < DATE_SUB(NOW(), INTERVAL :days_p DAY)
                ) AS total'
        );
        $staleSummaryStmt->bindValue(':days_f', $staleDays, PDO::PARAM_INT);
        $staleSummaryStmt->bindValue(':days_p', $staleDays, PDO::PARAM_INT);
        $staleSummaryStmt->execute();
        $staleSummary = $staleSummaryStmt->fetch();
        $summary['stale_updates'] = (int) ($staleSummary['total'] ?? 0);

        $docItemsStmt = $this->pdo->prepare(
            'SELECT id, responsible_name, documentation_status, neighborhood, city, updated_at
             FROM families
             WHERE documentation_status IN (\'pendente\', \'parcial\')' . $summaryWhere . '
             ORDER BY updated_at ASC, id ASC
             LIMIT 200'
        );
        $docItemsStmt->execute($summaryParams);
        $docItems = $docItemsStmt->fetchAll();

        $visitItemsStmt = $this->pdo->prepare(
            'SELECT
                v.id, v.status, v.scheduled_date, v.updated_at,
                f.responsible_name AS family_name,
                p.full_name AS person_name
             FROM visits v
             LEFT JOIN families f ON f.id = v.family_id
             LEFT JOIN people p ON p.id = v.person_id
             WHERE v.status IN (\'pendente\', \'agendada\')' .
            ($neighborhood !== '' ? ' AND f.neighborhood = :neighborhood' : '') . '
             ORDER BY v.scheduled_date ASC, v.id ASC
             LIMIT 200'
        );
        $visitItemsStmt->execute($summaryParams);
        $visitItems = $visitItemsStmt->fetchAll();

        $overdueItemsStmt = $this->pdo->prepare(
            'SELECT
                el.id, el.due_date, el.loan_date,
                e.code AS equipment_code, e.type AS equipment_type,
                f.responsible_name AS family_name,
                p.full_name AS person_name
             FROM equipment_loans el
             INNER JOIN equipment e ON e.id = el.equipment_id
             LEFT JOIN families f ON f.id = el.family_id
             LEFT JOIN people p ON p.id = el.person_id
             WHERE el.return_date IS NULL
               AND el.due_date < CURDATE()' .
            ($neighborhood !== '' ? ' AND f.neighborhood = :neighborhood' : '') . '
             ORDER BY el.due_date ASC, el.id ASC
             LIMIT 200'
        );
        $overdueItemsStmt->execute($summaryParams);
        $overdueItems = $overdueItemsStmt->fetchAll();

        return [
            'summary' => $summary,
            'items' => [
                'documentation' => is_array($docItems) ? $docItems : [],
                'visits' => is_array($visitItems) ? $visitItems : [],
                'overdue_returns' => is_array($overdueItems) ? $overdueItems : [],
            ],
        ];
    }
}

