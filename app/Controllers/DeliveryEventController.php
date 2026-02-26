<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\DeliveryEventModel;
use PDO;
use Throwable;

final class DeliveryEventController
{
    private const STATUSES = ['rascunho', 'aberto', 'concluido'];

    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
            'month' => trim((string) ($_GET['month'] ?? '')),
        ];

        try {
            $events = $this->model()->search($filters);
        } catch (Throwable $exception) {
            $events = [];
        }

        View::render('delivery_events.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Eventos de entrega',
            'activeMenu' => 'entregas',
            'authUser' => Session::get('auth_user', []),
            'events' => $events,
            'filters' => $filters,
            'statuses' => self::STATUSES,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function create(): void
    {
        $old = Session::consumeFlash('form_old');
        $event = [
            'name' => '',
            'event_date' => date('Y-m-d'),
            'block_multiple_same_month' => 1,
            'max_baskets' => '',
            'status' => 'rascunho',
        ];
        if (is_array($old)) {
            $event = array_merge($event, $old);
        }

        $this->renderForm('create', $event);
    }

    public function store(): void
    {
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/delivery-events/create');
        }

        try {
            $this->model()->create($this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao criar evento de entrega.');
            Session::flash('form_old', $input);
            Response::redirect('/delivery-events/create');
        }

        Session::flash('success', 'Evento de entrega criado com sucesso.');
        Response::redirect('/delivery-events');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Evento invalido.');
            Response::redirect('/delivery-events');
        }

        try {
            $event = $this->model()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar evento.');
            Response::redirect('/delivery-events');
        }

        if ($event === null) {
            Session::flash('error', 'Evento nao encontrado.');
            Response::redirect('/delivery-events');
        }

        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $event = array_merge($event, $old);
        }

        $this->renderForm('edit', $event);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Evento invalido.');
            Response::redirect('/delivery-events');
        }

        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/delivery-events/edit?id=' . $id);
        }

        try {
            if ($this->model()->findById($id) === null) {
                Session::flash('error', 'Evento nao encontrado.');
                Response::redirect('/delivery-events');
            }
            $data = $this->toPersistenceData($input);
            unset($data['created_by']);
            $this->model()->update($id, $data);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar evento.');
            Session::flash('form_old', $input);
            Response::redirect('/delivery-events/edit?id=' . $id);
        }

        Session::flash('success', 'Evento de entrega atualizado com sucesso.');
        Response::redirect('/delivery-events');
    }

    private function renderForm(string $mode, array $event): void
    {
        View::render('delivery_events.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar evento de entrega' : 'Novo evento de entrega',
            'activeMenu' => 'entregas',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'event' => $event,
            'statuses' => self::STATUSES,
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function sanitizeInput(array $post): array
    {
        $max = trim((string) ($post['max_baskets'] ?? ''));
        return [
            'name' => trim((string) ($post['name'] ?? '')),
            'event_date' => trim((string) ($post['event_date'] ?? '')),
            'block_multiple_same_month' => isset($post['block_multiple_same_month']) ? 1 : 0,
            'max_baskets' => $max === '' ? null : max(1, (int) $max),
            'status' => trim((string) ($post['status'] ?? 'rascunho')),
        ];
    }

    private function validateInput(array $input): ?string
    {
        if (trim((string) ($input['name'] ?? '')) === '') {
            return 'Nome do evento obrigatorio.';
        }
        if (trim((string) ($input['event_date'] ?? '')) === '') {
            return 'Data do evento obrigatoria.';
        }
        if (!in_array((string) ($input['status'] ?? ''), self::STATUSES, true)) {
            return 'Status do evento invalido.';
        }
        return null;
    }

    private function toPersistenceData(array $input): array
    {
        $authUser = Session::get('auth_user', []);
        $createdBy = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;

        return [
            'name' => $input['name'],
            'event_date' => $input['event_date'],
            'block_multiple_same_month' => (int) $input['block_multiple_same_month'],
            'max_baskets' => $input['max_baskets'],
            'status' => $input['status'],
            'created_by' => $createdBy,
        ];
    }

    private function model(): DeliveryEventModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new DeliveryEventModel($pdo);
    }
}
