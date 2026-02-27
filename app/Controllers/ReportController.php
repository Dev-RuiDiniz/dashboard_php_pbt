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
        return [
            'families' => $this->reportModel()->monthlyFamilies($filters),
            'baskets' => $this->reportModel()->monthlyBaskets($filters),
            'children' => $this->reportModel()->monthlyChildren($filters),
            'referrals' => $this->reportModel()->monthlyReferrals($filters),
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
