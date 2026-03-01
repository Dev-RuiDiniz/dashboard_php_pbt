<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Session;
use App\Core\View;
use App\Models\SocialRecordModel;
use PDO;
use Throwable;

final class SocialRecordController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'date_from' => trim((string) ($_GET['date_from'] ?? '')),
            'date_to' => trim((string) ($_GET['date_to'] ?? '')),
            'has_family' => trim((string) ($_GET['has_family'] ?? '')),
            'spiritual' => trim((string) ($_GET['spiritual'] ?? '')),
        ];

        try {
            $records = $this->model()->search($filters);
            $summary = $this->model()->summary($filters);
        } catch (Throwable $exception) {
            $records = [];
            $summary = [];
        }

        View::render('social_records.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Fichas sociais',
            'activeMenu' => 'fichas',
            'authUser' => Session::get('auth_user', []),
            'records' => $records,
            'summary' => $summary,
            'filters' => $filters,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function model(): SocialRecordModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new SocialRecordModel($pdo);
    }
}
