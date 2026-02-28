<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ReportModel;
use App\Services\AuditService;
use Dompdf\Dompdf;
use Dompdf\Options;
use PDO;
use Throwable;

final class ReportController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = $this->sanitizeFilters($_GET);

        try {
            $reportData = $this->buildReportData($filters);
            $auth = Session::get('auth_user', []);
            $userId = is_array($auth) ? (int) ($auth['id'] ?? 0) : null;
            $this->audit()->log('report.view', 'reports', null, $userId, $filters);
        } catch (Throwable $exception) {
            $reportData = [
                'families' => ['summary' => [], 'items' => []],
                'baskets' => ['summary' => [], 'items' => []],
                'children' => ['summary' => [], 'items' => []],
                'referrals' => ['summary' => [], 'items' => []],
                'equipment' => ['summary' => [], 'items' => []],
                'pendencies' => ['summary' => [], 'items' => []],
            ];
        }

        View::render('reports.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Relatorios mensais',
            'activeMenu' => 'relatorios',
            'authUser' => Session::get('auth_user', []),
            'filters' => $filters,
            'reportData' => $reportData,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function exportPdf(): void
    {
        $filters = $this->sanitizeFilters($_GET);

        try {
            $reportData = $this->buildReportData($filters);
            $html = $this->renderPdfHtml($filters, $reportData);
            $auth = Session::get('auth_user', []);
            $userId = is_array($auth) ? (int) ($auth['id'] ?? 0) : null;
            $this->audit()->log('report.export_pdf', 'reports', null, $userId, $filters);

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $filename = 'relatorio_mensal_' . $filters['period_start'] . '_' . $filters['period_end'] . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            echo $dompdf->output();
            exit;
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao exportar PDF do relatorio.');
            Response::redirect('/reports?' . http_build_query($filters));
        }
    }

    public function exportCsv(): void
    {
        $filters = $this->sanitizeFilters($_GET);

        try {
            $reportData = $this->buildReportData($filters);
            $auth = Session::get('auth_user', []);
            $userId = is_array($auth) ? (int) ($auth['id'] ?? 0) : null;
            $this->audit()->log('report.export_csv', 'reports', null, $userId, $filters);

            $filename = 'relatorio_mensal_' . $filters['period_start'] . '_' . $filters['period_end'] . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            echo "\xEF\xBB\xBF";
            $out = fopen('php://output', 'wb');
            if ($out === false) {
                throw new \RuntimeException('Falha ao abrir stream CSV.');
            }

            fputcsv($out, ['secao', 'campo_1', 'campo_2', 'campo_3', 'campo_4', 'campo_5'], ';');
            foreach ($this->buildExportRows($filters, $reportData) as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
            exit;
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao exportar CSV do relatorio.');
            Response::redirect('/reports?' . http_build_query($filters));
        }
    }

    public function exportExcel(): void
    {
        $filters = $this->sanitizeFilters($_GET);

        try {
            $reportData = $this->buildReportData($filters);
            $auth = Session::get('auth_user', []);
            $userId = is_array($auth) ? (int) ($auth['id'] ?? 0) : null;
            $this->audit()->log('report.export_excel', 'reports', null, $userId, $filters);

            $rows = $this->buildExportRows($filters, $reportData);
            $filename = 'relatorio_mensal_' . $filters['period_start'] . '_' . $filters['period_end'] . '.xls';

            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo '<html><head><meta charset="UTF-8"></head><body>';
            echo '<table border="1" cellspacing="0" cellpadding="4">';
            echo '<tr><th>secao</th><th>campo_1</th><th>campo_2</th><th>campo_3</th><th>campo_4</th><th>campo_5</th></tr>';
            foreach ($rows as $row) {
                echo '<tr>';
                for ($i = 0; $i < 6; $i++) {
                    $cell = (string) ($row[$i] ?? '');
                    echo '<td>' . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                echo '</tr>';
            }
            echo '</table></body></html>';
            exit;
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao exportar Excel do relatorio.');
            Response::redirect('/reports?' . http_build_query($filters));
        }
    }

    private function sanitizeFilters(array $input): array
    {
        $periodStart = trim((string) ($input['period_start'] ?? ''));
        $periodEnd = trim((string) ($input['period_end'] ?? ''));
        if ($periodStart === '' || $periodEnd === '') {
            $periodStart = date('Y-m-01');
            $periodEnd = date('Y-m-t');
        }
        if ($periodEnd < $periodStart) {
            [$periodStart, $periodEnd] = [$periodEnd, $periodStart];
        }

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => trim((string) ($input['status'] ?? '')),
            'neighborhood' => trim((string) ($input['neighborhood'] ?? '')),
        ];
    }

    private function buildReportData(array $filters): array
    {
        $staleDays = (int) (($this->container->get('config')['app']['alerts']['stale_days'] ?? 30));

        return [
            'families' => $this->reportModel()->monthlyFamilies($filters),
            'baskets' => $this->reportModel()->monthlyBaskets($filters),
            'children' => $this->reportModel()->monthlyChildren($filters),
            'referrals' => $this->reportModel()->monthlyReferrals($filters),
            'equipment' => $this->reportModel()->monthlyEquipment($filters),
            'pendencies' => $this->reportModel()->monthlyPendencies($filters, $staleDays),
        ];
    }

    private function renderPdfHtml(array $filters, array $reportData): string
    {
        ob_start();
        View::render('reports.pdf', [
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'filters' => $filters,
            'reportData' => $reportData,
        ]);
        return (string) ob_get_clean();
    }

    private function buildExportRows(array $filters, array $reportData): array
    {
        $families = is_array($reportData['families'] ?? null) ? $reportData['families'] : ['summary' => [], 'items' => []];
        $baskets = is_array($reportData['baskets'] ?? null) ? $reportData['baskets'] : ['summary' => [], 'items' => []];
        $children = is_array($reportData['children'] ?? null) ? $reportData['children'] : ['summary' => [], 'items' => []];
        $referrals = is_array($reportData['referrals'] ?? null) ? $reportData['referrals'] : ['summary' => [], 'items' => []];
        $equipment = is_array($reportData['equipment'] ?? null) ? $reportData['equipment'] : ['summary' => [], 'items' => []];
        $pendencies = is_array($reportData['pendencies'] ?? null) ? $reportData['pendencies'] : ['summary' => [], 'items' => []];
        $pendencyItems = is_array($pendencies['items'] ?? null) ? $pendencies['items'] : [];

        $rows = [];
        $rows[] = ['periodo', (string) ($filters['period_start'] ?? ''), (string) ($filters['period_end'] ?? ''), (string) ($filters['status'] ?? 'todos'), (string) ($filters['neighborhood'] ?? 'todos'), ''];
        $rows[] = ['resumo', 'familias', (string) ((int) (($families['summary']['total_families'] ?? 0))), 'cestas', (string) ((int) (($baskets['summary']['total_baskets'] ?? 0))), ''];
        $rows[] = ['resumo', 'criancas', (string) ((int) (($children['summary']['total_children'] ?? 0))), 'encaminhamentos', (string) ((int) (($referrals['summary']['total_referrals'] ?? 0))), ''];
        $rows[] = ['equipamentos', 'emprestados', (string) ((int) (($equipment['summary']['open_loans'] ?? 0))), 'devolvidos', (string) ((int) (($equipment['summary']['returned_loans'] ?? 0))), ''];
        $rows[] = ['equipamentos', 'atrasados', (string) ((int) (($equipment['summary']['overdue_loans'] ?? 0))), 'manutencao', (string) ((int) (($equipment['summary']['maintenance_equipment'] ?? 0))), ''];
        $rows[] = ['pendencias', 'documentacao', (string) ((int) (($pendencies['summary']['pending_documentation'] ?? 0))), 'visitas', (string) ((int) (($pendencies['summary']['pending_visits'] ?? 0))), ''];
        $rows[] = ['pendencias', 'devolucoes_atrasadas', (string) ((int) (($pendencies['summary']['overdue_returns'] ?? 0))), 'sem_atualizacao', (string) ((int) (($pendencies['summary']['stale_updates'] ?? 0))), ''];
        $rows[] = ['', '', '', '', '', ''];

        foreach (array_slice((array) ($families['items'] ?? []), 0, 20) as $item) {
            $rows[] = [
                'familias_item',
                (string) ($item['responsible_name'] ?? ''),
                (string) ($item['neighborhood'] ?? ''),
                (string) (((int) ($item['is_active'] ?? 0) === 1) ? 'ativo' : 'inativo'),
                (string) ($item['documentation_status'] ?? ''),
                (string) ($item['created_at'] ?? ''),
            ];
        }
        foreach (array_slice((array) ($baskets['items'] ?? []), 0, 20) as $item) {
            $rows[] = [
                'cestas_item',
                (string) ($item['event_name'] ?? ''),
                (string) ($item['family_name'] ?? ''),
                (string) ((int) ($item['quantity'] ?? 0)),
                (string) ($item['status'] ?? ''),
                (string) ($item['event_date'] ?? ''),
            ];
        }
        foreach (array_slice((array) ($children['items'] ?? []), 0, 20) as $item) {
            $rows[] = [
                'criancas_item',
                (string) ($item['name'] ?? ''),
                (string) ($item['family_name'] ?? ''),
                (string) ($item['birth_date'] ?? ''),
                (string) ($item['neighborhood'] ?? ''),
                (string) ($item['created_at'] ?? ''),
            ];
        }
        foreach (array_slice((array) ($referrals['items'] ?? []), 0, 20) as $item) {
            $rows[] = [
                'encaminhamentos_item',
                (string) ($item['referral_type'] ?? ''),
                (string) ($item['person_name'] ?? ''),
                (string) ($item['status'] ?? ''),
                (string) ($item['referral_date'] ?? ''),
                (string) ($item['family_name'] ?? ''),
            ];
        }
        foreach (array_slice((array) ($equipment['items'] ?? []), 0, 20) as $item) {
            $loanStatus = ((string) ($item['return_date'] ?? '') !== '')
                ? 'devolvido'
                : (((string) ($item['due_date'] ?? '') < date('Y-m-d')) ? 'atrasado' : 'emprestado');
            $rows[] = [
                'equipamentos_item',
                (string) ($item['equipment_code'] ?? ''),
                (string) ($item['equipment_type'] ?? ''),
                (string) ($item['family_name'] ?? $item['person_name'] ?? ''),
                $loanStatus,
                (string) ($item['due_date'] ?? ''),
            ];
        }

        foreach (array_slice((array) ($pendencyItems['documentation'] ?? []), 0, 20) as $item) {
            $rows[] = [
                'pendencia_documentacao',
                (string) ($item['responsible_name'] ?? ''),
                (string) ($item['documentation_status'] ?? ''),
                (string) ($item['neighborhood'] ?? ''),
                (string) ($item['city'] ?? ''),
                (string) ($item['updated_at'] ?? ''),
            ];
        }
        foreach (array_slice((array) ($pendencyItems['visits'] ?? []), 0, 20) as $item) {
            $target = (string) (($item['family_name'] ?? '') ?: ($item['person_name'] ?? ''));
            $rows[] = [
                'pendencia_visita',
                $target,
                (string) ($item['status'] ?? ''),
                (string) ($item['scheduled_date'] ?? ''),
                '',
                (string) ($item['updated_at'] ?? ''),
            ];
        }
        foreach (array_slice((array) ($pendencyItems['overdue_returns'] ?? []), 0, 20) as $item) {
            $target = (string) (($item['family_name'] ?? '') ?: ($item['person_name'] ?? ''));
            $rows[] = [
                'pendencia_devolucao',
                (string) ($item['equipment_code'] ?? ''),
                (string) ($item['equipment_type'] ?? ''),
                $target,
                (string) ($item['due_date'] ?? ''),
                (string) ($item['loan_date'] ?? ''),
            ];
        }

        return $rows;
    }

    private function reportModel(): ReportModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new ReportModel($pdo);
    }

    private function audit(): AuditService
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new AuditService($pdo);
    }
}
