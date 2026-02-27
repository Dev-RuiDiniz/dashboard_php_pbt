<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\EquipmentLoanModel;
use App\Models\EquipmentModel;
use App\Models\FamilyModel;
use App\Models\PersonModel;
use PDO;
use Throwable;

final class EquipmentLoanController
{
    private const RETURN_CONDITIONS = ['bom', 'regular', 'ruim'];

    public function __construct(private readonly Container $container)
    {
    }

    public function index(): void
    {
        $filters = [
            'equipment_code' => trim((string) ($_GET['equipment_code'] ?? '')),
            'status' => trim((string) ($_GET['status'] ?? '')),
        ];

        try {
            $loans = $this->loanModel()->search($filters);
            $availableEquipment = $this->equipmentModel()->listAvailable();
            $families = $this->familyModel()->search(['status' => 'ativo']);
            $people = $this->personModel()->search([]);
            $overdueCount = $this->loanModel()->countOverdue();
            $overdueLoans = $this->loanModel()->listOverdue(10);
        } catch (Throwable $exception) {
            $loans = [];
            $availableEquipment = [];
            $families = [];
            $people = [];
            $overdueCount = 0;
            $overdueLoans = [];
        }

        $loanForm = [
            'equipment_id' => 0,
            'target_type' => 'family',
            'family_id' => 0,
            'person_id' => 0,
            'loan_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'notes' => '',
        ];
        $oldLoanForm = Session::consumeFlash('loan_form_old');
        if (is_array($oldLoanForm)) {
            $loanForm = array_merge($loanForm, $oldLoanForm);
        }

        View::render('equipment/loans', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Emprestimos de equipamentos',
            'activeMenu' => 'equipamentos',
            'authUser' => Session::get('auth_user', []),
            'filters' => $filters,
            'loans' => $loans,
            'loanForm' => $loanForm,
            'availableEquipment' => $availableEquipment,
            'families' => $families,
            'people' => $people,
            'returnConditions' => self::RETURN_CONDITIONS,
            'overdueCount' => $overdueCount,
            'overdueLoans' => $overdueLoans,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function store(): void
    {
        $input = $this->sanitizeLoanInput($_POST);
        $error = $this->validateLoanInput($input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('loan_form_old', $input);
            Response::redirect('/equipment-loans');
        }

        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        try {
            $equipment = $this->equipmentModel()->findById((int) $input['equipment_id']);
            if ($equipment === null) {
                Session::flash('error', 'Equipamento nao encontrado.');
                Session::flash('loan_form_old', $input);
                Response::redirect('/equipment-loans');
            }
            if ((string) ($equipment['status'] ?? '') !== 'disponivel') {
                Session::flash('error', 'Equipamento indisponivel para emprestimo.');
                Session::flash('loan_form_old', $input);
                Response::redirect('/equipment-loans');
            }
            if ($this->loanModel()->hasOpenLoanByEquipmentId((int) $input['equipment_id'])) {
                Session::flash('error', 'Ja existe emprestimo em aberto para este equipamento.');
                Session::flash('loan_form_old', $input);
                Response::redirect('/equipment-loans');
            }

            if ($input['target_type'] === 'family' && $this->familyModel()->findById((int) $input['family_id']) === null) {
                Session::flash('error', 'Familia nao encontrada.');
                Session::flash('loan_form_old', $input);
                Response::redirect('/equipment-loans');
            }
            if ($input['target_type'] === 'person' && $this->personModel()->findById((int) $input['person_id']) === null) {
                Session::flash('error', 'Pessoa nao encontrada.');
                Session::flash('loan_form_old', $input);
                Response::redirect('/equipment-loans');
            }

            $authUser = Session::get('auth_user', []);
            $createdBy = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;

            $pdo->beginTransaction();
            $this->loanModel()->create([
                'equipment_id' => (int) $input['equipment_id'],
                'family_id' => $input['target_type'] === 'family' ? (int) $input['family_id'] : null,
                'person_id' => $input['target_type'] === 'person' ? (int) $input['person_id'] : null,
                'loan_date' => $input['loan_date'],
                'due_date' => $input['due_date'],
                'return_date' => null,
                'return_condition' => null,
                'notes' => $input['notes'] !== '' ? $input['notes'] : null,
                'created_by' => $createdBy,
            ]);
            $this->equipmentModel()->updateStatus((int) $input['equipment_id'], 'emprestado');
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Session::flash('error', 'Falha ao registrar emprestimo.');
            Session::flash('loan_form_old', $input);
            Response::redirect('/equipment-loans');
        }

        Session::flash('success', 'Emprestimo registrado e equipamento marcado como emprestado.');
        Response::redirect('/equipment-loans');
    }

    public function returnLoan(): void
    {
        $loanId = (int) ($_GET['id'] ?? 0);
        $returnDate = trim((string) ($_POST['return_date'] ?? date('Y-m-d')));
        $returnCondition = trim((string) ($_POST['return_condition'] ?? 'bom'));
        $returnNotes = trim((string) ($_POST['return_notes'] ?? ''));

        if ($loanId <= 0) {
            Session::flash('error', 'Emprestimo invalido.');
            Response::redirect('/equipment-loans');
        }
        if ($returnDate === '') {
            Session::flash('error', 'Data de devolucao obrigatoria.');
            Response::redirect('/equipment-loans');
        }
        if (!in_array($returnCondition, self::RETURN_CONDITIONS, true)) {
            Session::flash('error', 'Estado de devolucao invalido.');
            Response::redirect('/equipment-loans');
        }

        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        try {
            $loan = $this->loanModel()->findById($loanId);
            if ($loan === null) {
                Session::flash('error', 'Emprestimo nao encontrado.');
                Response::redirect('/equipment-loans');
            }
            if (($loan['return_date'] ?? null) !== null) {
                Session::flash('error', 'Este emprestimo ja foi devolvido.');
                Response::redirect('/equipment-loans');
            }
            if ($returnDate < (string) ($loan['loan_date'] ?? $returnDate)) {
                Session::flash('error', 'Data de devolucao nao pode ser anterior ao emprestimo.');
                Response::redirect('/equipment-loans');
            }

            $pdo->beginTransaction();
            $newNotes = trim((string) ($loan['notes'] ?? ''));
            if ($returnNotes !== '') {
                $newNotes = $newNotes !== '' ? ($newNotes . PHP_EOL . 'Devolucao: ' . $returnNotes) : ('Devolucao: ' . $returnNotes);
            }
            $this->loanModel()->returnLoan($loanId, [
                'return_date' => $returnDate,
                'return_condition' => $returnCondition,
                'notes' => $newNotes !== '' ? $newNotes : null,
            ]);
            $this->equipmentModel()->updateStatusAndCondition((int) $loan['equipment_id'], 'disponivel', $returnCondition);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Session::flash('error', 'Falha ao registrar devolucao.');
            Response::redirect('/equipment-loans');
        }

        Session::flash('success', 'Devolucao registrada e equipamento atualizado para disponivel.');
        Response::redirect('/equipment-loans');
    }

    private function sanitizeLoanInput(array $post): array
    {
        return [
            'equipment_id' => (int) ($post['equipment_id'] ?? 0),
            'target_type' => trim((string) ($post['target_type'] ?? 'family')),
            'family_id' => (int) ($post['family_id'] ?? 0),
            'person_id' => (int) ($post['person_id'] ?? 0),
            'loan_date' => trim((string) ($post['loan_date'] ?? '')),
            'due_date' => trim((string) ($post['due_date'] ?? '')),
            'notes' => trim((string) ($post['notes'] ?? '')),
        ];
    }

    private function validateLoanInput(array $input): ?string
    {
        if ((int) ($input['equipment_id'] ?? 0) <= 0) {
            return 'Selecione o equipamento.';
        }
        if (!in_array((string) ($input['target_type'] ?? ''), ['family', 'person'], true)) {
            return 'Tipo de destino invalido.';
        }
        if ($input['target_type'] === 'family' && (int) ($input['family_id'] ?? 0) <= 0) {
            return 'Selecione a familia para emprestimo.';
        }
        if ($input['target_type'] === 'person' && (int) ($input['person_id'] ?? 0) <= 0) {
            return 'Selecione a pessoa para emprestimo.';
        }
        if ((string) ($input['loan_date'] ?? '') === '') {
            return 'Data de emprestimo obrigatoria.';
        }
        if ((string) ($input['due_date'] ?? '') === '') {
            return 'Data prevista de devolucao obrigatoria.';
        }
        if ((string) $input['due_date'] < (string) $input['loan_date']) {
            return 'Data prevista de devolucao nao pode ser anterior ao emprestimo.';
        }
        return null;
    }

    private function loanModel(): EquipmentLoanModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new EquipmentLoanModel($pdo);
    }

    private function equipmentModel(): EquipmentModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new EquipmentModel($pdo);
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
}

