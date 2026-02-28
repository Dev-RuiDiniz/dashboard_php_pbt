<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Session;
use App\Core\View;
use App\Models\DashboardModel;
use App\Models\EquipmentLoanModel;
use App\Models\VisitModel;
use PDO;
use Throwable;

final class DashboardController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $user = Session::get('auth_user', []);
        $staleDays = (int) (($this->container->get('config')['app']['alerts']['stale_days'] ?? 30));
        $periodStart = date('Y-m-01');
        $periodEnd = date('Y-m-t');

        try {
            $summary = $this->dashboardModel()->summary($periodStart, $periodEnd);
            $pendingDocsCount = $this->visitModel()->countPendingDocumentation();
            $pendingDocs = $this->visitModel()->listPendingDocumentation(5);
            $pendingVisitsCount = $this->visitModel()->countPendingVisits();
            $pendingVisits = $this->visitModel()->listPendingVisits(5);
            $staleFamiliesCount = $this->dashboardModel()->countStaleFamilies($staleDays);
            $staleFamilies = $this->dashboardModel()->listStaleFamilies($staleDays, 5);
            $overdueLoansCount = $this->loanModel()->countOverdue();
            $overdueLoans = $this->loanModel()->listOverdue(5);
        } catch (Throwable $exception) {
            $summary = [];
            $pendingDocsCount = 0;
            $pendingDocs = [];
            $pendingVisitsCount = 0;
            $pendingVisits = [];
            $staleFamiliesCount = 0;
            $staleFamilies = [];
            $overdueLoansCount = 0;
            $overdueLoans = [];
        }

        View::render('dashboard.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Dashboard',
            'activeMenu' => 'dashboard',
            'user' => is_array($user) ? $user : [],
            'authUser' => is_array($user) ? $user : [],
            'summary' => $summary,
            'pendingDocsCount' => $pendingDocsCount,
            'pendingDocs' => $pendingDocs,
            'pendingVisitsCount' => $pendingVisitsCount,
            'pendingVisits' => $pendingVisits,
            'staleFamiliesCount' => $staleFamiliesCount,
            'staleFamilies' => $staleFamilies,
            'overdueLoansCount' => $overdueLoansCount,
            'overdueLoans' => $overdueLoans,
            'staleDays' => $staleDays,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'success' => Session::consumeFlash('success'),
        ]);
    }

    private function dashboardModel(): DashboardModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new DashboardModel($pdo);
    }

    private function visitModel(): VisitModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new VisitModel($pdo);
    }

    private function loanModel(): EquipmentLoanModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new EquipmentLoanModel($pdo);
    }
}
