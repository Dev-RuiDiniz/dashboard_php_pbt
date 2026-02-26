<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\ChildModel;
use App\Models\FamilyModel;
use PDO;
use Throwable;

final class ChildController
{
    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'q' => trim((string) ($_GET['q'] ?? '')),
            'family_id' => (int) ($_GET['family_id'] ?? 0),
            'city' => trim((string) ($_GET['city'] ?? '')),
        ];

        try {
            $children = $this->childModel()->search($filters);
            $families = $this->familyModel()->search([]);
        } catch (Throwable $exception) {
            $children = [];
            $families = [];
        }

        View::render('children.index', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Criancas',
            'activeMenu' => 'criancas',
            'authUser' => Session::get('auth_user', []),
            'children' => $children,
            'families' => $families,
            'filters' => $filters,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function create(): void
    {
        $familyId = (int) ($_GET['family_id'] ?? 0);
        $old = Session::consumeFlash('form_old');
        $child = [
            'family_id' => $familyId > 0 ? $familyId : 0,
            'name' => '',
            'birth_date' => '',
            'age_years' => '',
            'relationship' => '',
            'notes' => '',
        ];
        if (is_array($old)) {
            $child = array_merge($child, $old);
        }

        $this->renderForm('create', $child);
    }

    public function store(): void
    {
        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/children/create' . ($input['family_id'] > 0 ? '?family_id=' . $input['family_id'] : ''));
        }

        try {
            if ($this->familyModel()->findById((int) $input['family_id']) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/children');
            }
            $this->childModel()->create($this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao cadastrar crianca.');
            Session::flash('form_old', $input);
            Response::redirect('/children/create');
        }

        Session::flash('success', 'Crianca cadastrada com sucesso.');
        Response::redirect('/children');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Crianca invalida.');
            Response::redirect('/children');
        }

        try {
            $child = $this->childModel()->findById($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar crianca.');
            Response::redirect('/children');
        }

        if ($child === null) {
            Session::flash('error', 'Crianca nao encontrada.');
            Response::redirect('/children');
        }

        $old = Session::consumeFlash('form_old');
        if (is_array($old)) {
            $child = array_merge($child, $old);
        }

        $this->renderForm('edit', $child);
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Crianca invalida.');
            Response::redirect('/children');
        }

        $input = $this->sanitizeInput($_POST);
        $error = $this->validateInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('form_old', $input);
            Response::redirect('/children/edit?id=' . $id);
        }

        try {
            if ($this->childModel()->findById($id) === null) {
                Session::flash('error', 'Crianca nao encontrada.');
                Response::redirect('/children');
            }
            if ($this->familyModel()->findById((int) $input['family_id']) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Response::redirect('/children');
            }
            $this->childModel()->update($id, $this->toPersistenceData($input));
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar crianca.');
            Session::flash('form_old', $input);
            Response::redirect('/children/edit?id=' . $id);
        }

        Session::flash('success', 'Crianca atualizada com sucesso.');
        Response::redirect('/children');
    }

    public function delete(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Crianca invalida.');
            Response::redirect('/children');
        }

        try {
            $child = $this->childModel()->findById($id);
            $familyId = (int) ($child['family_id'] ?? 0);
            $this->childModel()->delete($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao remover crianca.');
            Response::redirect('/children');
        }

        Session::flash('success', 'Crianca removida com sucesso.');
        if (isset($_GET['back']) && $_GET['back'] === 'family' && $familyId > 0) {
            Response::redirect('/families/show?id=' . $familyId);
        }
        Response::redirect('/children');
    }

    private function renderForm(string $mode, array $child): void
    {
        try {
            $families = $this->familyModel()->search([]);
        } catch (Throwable $exception) {
            $families = [];
        }

        View::render('children.form', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => $mode === 'edit' ? 'Editar crianca' : 'Nova crianca',
            'activeMenu' => 'criancas',
            'authUser' => Session::get('auth_user', []),
            'mode' => $mode,
            'child' => $child,
            'families' => $families,
            'error' => Session::consumeFlash('error'),
        ]);
    }

    private function sanitizeInput(array $post): array
    {
        $age = trim((string) ($post['age_years'] ?? ''));
        return [
            'family_id' => (int) ($post['family_id'] ?? 0),
            'name' => trim((string) ($post['name'] ?? '')),
            'birth_date' => trim((string) ($post['birth_date'] ?? '')),
            'age_years' => $age === '' ? null : max(0, (int) $age),
            'relationship' => trim((string) ($post['relationship'] ?? '')),
            'notes' => trim((string) ($post['notes'] ?? '')),
        ];
    }

    private function validateInput(array $input): ?string
    {
        if ((int) ($input['family_id'] ?? 0) <= 0) {
            return 'Selecione a familia vinculada.';
        }
        if (trim((string) ($input['name'] ?? '')) === '') {
            return 'Nome da crianca e obrigatorio.';
        }
        return null;
    }

    private function toPersistenceData(array $input): array
    {
        return [
            'family_id' => (int) $input['family_id'],
            'name' => $input['name'],
            'birth_date' => ($input['birth_date'] ?? '') !== '' ? $input['birth_date'] : null,
            'age_years' => $input['age_years'],
            'relationship' => ($input['relationship'] ?? '') !== '' ? $input['relationship'] : null,
            'notes' => ($input['notes'] ?? '') !== '' ? $input['notes'] : null,
        ];
    }

    private function childModel(): ChildModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new ChildModel($pdo);
    }

    private function familyModel(): FamilyModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new FamilyModel($pdo);
    }
}

