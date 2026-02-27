<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\EquipmentModel;
use PDO;
use Throwable;

final class EquipmentController
{
    private const STATUSES = ['disponivel', 'emprestado', 'manutencao', 'inativo'];
    private const CONDITIONS = ['bom', 'regular', 'ruim'];
    private const TYPES = ['cadeira_rodas', 'muleta', 'andador', 'cama_hospitalar', 'outro'];

    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'code' => trim((string) ($_GET['code'] ?? '')),
            'type' => trim((string) ($_GET['type'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
        ];

        try {
            $equipments = $this->equipmentModel()->search($filters);
        } catch (Throwable $exception) {
            $equipments = [];
        }

        View::render('equipment.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Equipamentos',
            'activeMenu' => 'equipamentos',
            'authUser' => Session::get('auth_user', []),
            'equipments' => $equipments,
            'filters' => $filters,
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function create(): void
    {
        $old = Session::consumeFlash('form_old');
        $equipment = [
            'type' => 'cadeira_rodas',
            'condition_state' => 'bom',
            'status' => 'disponivel',
            'notes' => '',
        ];
        if (is_array($old)) {
            $equipment = array_merge($equipment, $old);
        }

        $this->renderForm('create', $equipment, null);
    }

    public function store(): void
    {
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/equipment/create');
        }

        try {
            $code = $this->equipmentModel()->nextCodeByType((string) $input['type']);
            while ($this->equipmentModel()->existsCode($code)) {
                $code = $this->equipmentModel()->nextCodeByType((string) $input['type']);
            }
            $this->equipmentModel()->create($this->toPersistenceData($input, $code));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao cadastrar equipamento.');
            Session::flash('form_old', $input);
            Response::redirect('/equipment/create');
        }

        Session::flash('success', 'Equipamento cadastrado com sucesso.');
        Response::redirect('/equipment');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Equipamento invalido.');
            Response::redirect('/equipment');
        }

        try {
            $equipment = $this->equipmentModel()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar equipamento.');
            Response::redirect('/equipment');
        }

        if ($equipment === null) {
            Session::flash('error', 'Equipamento nao encontrado.');
            Response::redirect('/equipment');
        }

        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $equipment = array_merge($equipment, $old);
        }

        $this->renderForm('edit', $equipment, (string) ($equipment['code'] ?? ''));
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Equipamento invalido.');
            Response::redirect('/equipment');
        }

        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/equipment/edit?id=' . $id);
        }

        try {
            $equipment = $this->equipmentModel()->findById($id);
            if ($equipment === null) {
                Session::flash('error', 'Equipamento nao encontrado.');
                Response::redirect('/equipment');
            }
            $this->equipmentModel()->update($id, $this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar equipamento.');
            Session::flash('form_old', $input);
            Response::redirect('/equipment/edit?id=' . $id);
        }

        Session::flash('success', 'Equipamento atualizado com sucesso.');
        Response::redirect('/equipment');
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Equipamento invalido.');
            Response::redirect('/equipment');
        }

        try {
            if ($this->equipmentModel()->findById($id) === null) {
                Session::flash('error', 'Equipamento nao encontrado.');
                Response::redirect('/equipment');
            }
            $this->equipmentModel()->delete($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover equipamento. Verifique se existe emprestimo vinculado.');
            Response::redirect('/equipment');
        }

        Session::flash('success', 'Equipamento removido com sucesso.');
        Response::redirect('/equipment');
    }

    private function renderForm(string $mode, array $equipment, ?string $code): void
    {
        View::render('equipment.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar equipamento' : 'Novo equipamento',
            'activeMenu' => 'equipamentos',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'equipment' => $equipment,
            'equipmentCode' => $code,
            'types' => self::TYPES,
            'conditions' => self::CONDITIONS,
            'statuses' => self::STATUSES,
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function sanitizeInput(array $post): array
    {
        return [
            'type' => trim((string) ($post['type'] ?? '')),
            'condition_state' => trim((string) ($post['condition_state'] ?? 'bom')),
            'status' => trim((string) ($post['status'] ?? 'disponivel')),
            'notes' => trim((string) ($post['notes'] ?? '')),
        ];
    }

    private function validateInput(array $input): ?string
    {
        if (!in_array((string) ($input['type'] ?? ''), self::TYPES, true)) {
            return 'Tipo de equipamento invalido.';
        }
        if (!in_array((string) ($input['condition_state'] ?? ''), self::CONDITIONS, true)) {
            return 'Estado de conservacao invalido.';
        }
        if (!in_array((string) ($input['status'] ?? ''), self::STATUSES, true)) {
            return 'Status de equipamento invalido.';
        }
        return null;
    }

    private function toPersistenceData(array $input, ?string $code = null): array
    {
        $data = [
            'type' => $input['type'],
            'condition_state' => $input['condition_state'],
            'status' => $input['status'],
            'notes' => ($input['notes'] ?? '') !== '' ? $input['notes'] : null,
        ];
        if ($code !== null) {
            $data['code'] = $code;
        }
        return $data;
    }

    private function equipmentModel(): EquipmentModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new EquipmentModel($pdo);
    }
}

