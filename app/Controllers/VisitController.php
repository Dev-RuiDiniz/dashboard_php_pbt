<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\FamilyModel;
use App\Models\PersonModel;
use App\Models\VisitModel;
use App\Services\AuditService;
use PDO;
use Throwable;

final class VisitController
{
    private const STATUSES = ['pendente', 'agendada', 'concluida', 'cancelada'];

    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'pendency' => trim((string) ($_GET['pendency'] ?? '')),
        ];
        $staleDays = (int) (($this->container->get('config')['app']['alerts']['stale_days'] ?? 30));

        try {
            $visits = $this->visitModel()->search($filters);
            $pendingDocsCount = $this->visitModel()->countPendingDocumentation();
            $pendingDocs = $this->visitModel()->listPendingDocumentation(8);
            $pendingVisitsCount = $this->visitModel()->countPendingVisits();
            $pendingVisits = $this->visitModel()->listPendingVisits(8);
            $staleUpdatesCount = $this->visitModel()->countWithoutUpdates($staleDays);
            $staleUpdates = $this->visitModel()->listWithoutUpdates($staleDays, 8);
        } catch (Throwable $exception) {
            $visits = [];
            $pendingDocsCount = 0;
            $pendingDocs = [];
            $pendingVisitsCount = 0;
            $pendingVisits = [];
            $staleUpdatesCount = 0;
            $staleUpdates = [];
        }

        View::render('visits.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Visitas',
            'activeMenu' => 'visitas',
            'authUser' => Session::get('auth_user', []),
            'visits' => $visits,
            'filters' => $filters,
            'statuses' => self::STATUSES,
            'pendingDocsCount' => $pendingDocsCount,
            'pendingDocs' => $pendingDocs,
            'pendingVisitsCount' => $pendingVisitsCount,
            'pendingVisits' => $pendingVisits,
            'staleUpdatesCount' => $staleUpdatesCount,
            'staleUpdates' => $staleUpdates,
            'staleDays' => $staleDays,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function create(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $personId = (int) ($_GET['person_id'] ?? 0);

        $visit = [
            'family_id' => $familyId > 0 ? $familyId : 0,
            'person_id' => $personId > 0 ? $personId : 0,
            'scheduled_date' => '',
            'status' => 'pendente',
            'notes' => '',
        ];
        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $visit = array_merge($visit, $old);
        }

        $this->renderForm('create', $visit);
    }

    public function store(): void
    {
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input, false);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/visits/create');
        }

        try {
            $this->assertTargets($input);
            $authUser = Session::get('auth_user', []);
            $requestedBy = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;

            $this->visitModel()->create([
                'family_id' => ((int) $input['family_id']) > 0 ? (int) $input['family_id'] : null,
                'person_id' => ((int) $input['person_id']) > 0 ? (int) $input['person_id'] : null,
                'requested_by' => $requestedBy,
                'requested_at' => date('Y-m-d H:i:s'),
                'scheduled_date' => $input['scheduled_date'] !== '' ? $input['scheduled_date'] : null,
                'completed_by' => null,
                'completed_at' => null,
                'notes' => $input['notes'] !== '' ? $input['notes'] : null,
                'status' => $input['status'],
            ]);
            $authUser = Session::get('auth_user', []);
            $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : null;
            $this->audit()->log('visit.create', 'visits', null, $userId, [
                'family_id' => ((int) $input['family_id']) > 0 ? (int) $input['family_id'] : null,
                'person_id' => ((int) $input['person_id']) > 0 ? (int) $input['person_id'] : null,
                'status' => $input['status'],
            ]);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao solicitar visita.');
            Session::flash('form_old', $input);
            Response::redirect('/visits/create');
        }

        Session::flash('success', 'Visita solicitada com sucesso.');
        Response::redirect('/visits');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Visita invalida.');
            Response::redirect('/visits');
        }

        try {
            $visit = $this->visitModel()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar visita.');
            Response::redirect('/visits');
        }

        if ($visit === null) {
            Session::flash('error', 'Visita nao encontrada.');
            Response::redirect('/visits');
        }

        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $visit = array_merge($visit, $old);
        }

        $this->renderForm('edit', $visit);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Visita invalida.');
            Response::redirect('/visits');
        }

        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input, true);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/visits/edit?id=' . $id);
        }

        try {
            $visit = $this->visitModel()->findById($id);
            if ($visit === null) {
                Session::flash('error', 'Visita nao encontrada.');
                Response::redirect('/visits');
            }
            if ((string) ($visit['status'] ?? '') === 'concluida') {
                Session::flash('error', 'Visita concluida nao pode ser editada.');
                Response::redirect('/visits');
            }

            $this->assertTargets($input);
            $this->visitModel()->update($id, [
                'family_id' => ((int) $input['family_id']) > 0 ? (int) $input['family_id'] : null,
                'person_id' => ((int) $input['person_id']) > 0 ? (int) $input['person_id'] : null,
                'scheduled_date' => $input['scheduled_date'] !== '' ? $input['scheduled_date'] : null,
                'notes' => $input['notes'] !== '' ? $input['notes'] : null,
                'status' => $input['status'],
            ]);
            $authUser = Session::get('auth_user', []);
            $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : null;
            $this->audit()->log('visit.update', 'visits', $id, $userId, [
                'status' => $input['status'],
                'scheduled_date' => $input['scheduled_date'] !== '' ? $input['scheduled_date'] : null,
            ]);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar visita.');
            Session::flash('form_old', $input);
            Response::redirect('/visits/edit?id=' . $id);
        }

        Session::flash('success', 'Visita atualizada com sucesso.');
        Response::redirect('/visits');
    }

    public function conclude(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $completionNotes = trim((string) ($_POST['completion_notes'] ?? ''));

        if ($id <= 0) {
            Session::flash('error', 'Visita invalida.');
            Response::redirect('/visits');
        }

        try {
            $visit = $this->visitModel()->findById($id);
            if ($visit === null) {
                Session::flash('error', 'Visita nao encontrada.');
                Response::redirect('/visits');
            }
            if ((string) ($visit['status'] ?? '') === 'concluida') {
                Session::flash('error', 'Visita ja concluida.');
                Response::redirect('/visits');
            }

            $authUser = Session::get('auth_user', []);
            $completedBy = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;
            $notes = trim((string) ($visit['notes'] ?? ''));
            if ($completionNotes !== '') {
                $notes = $notes !== '' ? ($notes . PHP_EOL . 'Conclusao: ' . $completionNotes) : ('Conclusao: ' . $completionNotes);
            }

            $this->visitModel()->conclude(
                $id,
                $completedBy,
                date('Y-m-d H:i:s'),
                $notes !== '' ? $notes : null
            );
            $this->audit()->log('visit.conclude', 'visits', $id, $completedBy, []);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao concluir visita.');
            Response::redirect('/visits');
        }

        Session::flash('success', 'Visita concluida com sucesso.');
        Response::redirect('/visits');
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Visita invalida.');
            Response::redirect('/visits');
        }

        try {
            $visit = $this->visitModel()->findById($id);
            if ($visit === null) {
                Session::flash('error', 'Visita nao encontrada.');
                Response::redirect('/visits');
            }
            $this->visitModel()->delete($id);
            $authUser = Session::get('auth_user', []);
            $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : null;
            $this->audit()->log('visit.delete', 'visits', $id, $userId, []);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover visita.');
            Response::redirect('/visits');
        }

        Session::flash('success', 'Visita removida com sucesso.');
        Response::redirect('/visits');
    }

    private function renderForm(string $mode, array $visit): void
    {
        try {
            $families = $this->familyModel()->search(['status' => 'ativo']);
            $people = $this->personModel()->search([]);
        } catch (Throwable $exception) {
            $families = [];
            $people = [];
        }

        View::render('visits.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar visita' : 'Solicitar visita',
            'activeMenu' => 'visitas',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'visit' => $visit,
            'families' => $families,
            'people' => $people,
            'statuses' => self::STATUSES,
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function sanitizeInput(array $post): array
    {
        return [
            'family_id' => (int) ($post['family_id'] ?? 0),
            'person_id' => (int) ($post['person_id'] ?? 0),
            'scheduled_date' => trim((string) ($post['scheduled_date'] ?? '')),
            'notes' => trim((string) ($post['notes'] ?? '')),
            'status' => trim((string) ($post['status'] ?? 'pendente')),
        ];
    }

    private function validateInput(array $input, bool $allowConcluida): ?string
    {
        if ((int) ($input['family_id'] ?? 0) <= 0 && (int) ($input['person_id'] ?? 0) <= 0) {
            return 'Selecione uma familia ou pessoa para a visita.';
        }
        if (!in_array((string) ($input['status'] ?? ''), self::STATUSES, true)) {
            return 'Status de visita invalido.';
        }
        if (!$allowConcluida && (string) $input['status'] === 'concluida') {
            return 'Solicitacao inicial nao pode ser concluida.';
        }
        return null;
    }

    private function assertTargets(array $input): void
    {
        $familyId = (int) ($input['family_id'] ?? 0);
        $personId = (int) ($input['person_id'] ?? 0);
        if ($familyId > 0 && $this->familyModel()->findById($familyId) === null) {
            throw new \RuntimeException('familia_nao_encontrada');
        }
        if ($personId > 0 && $this->personModel()->findById($personId) === null) {
            throw new \RuntimeException('pessoa_nao_encontrada');
        }
    }

    private function visitModel(): VisitModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new VisitModel($pdo);
    }

    private function familyModel(): FamilyModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new FamilyModel($pdo);
    }

    private function personModel(): PersonModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new PersonModel($pdo);
    }

    private function audit(): AuditService
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new AuditService($pdo);
    }
}
