<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Container;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\AuditLogModel;
use App\Models\ChildModel;
use App\Models\DeliveryModel;
use App\Models\DeliveryEventModel;
use App\Models\FamilyModel;
use App\Models\PersonModel;
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

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Evento invalido.');
            Response::redirect('/delivery-events');
        }

        try {
            $event = $this->model()->findById($id);
            if ($event === null) {
                Session::flash('error', 'Evento nao encontrado.');
                Response::redirect('/delivery-events');
            }

            $deliveries = $this->deliveryModel()->listByEventId($id);
            $families = $this->familyModel()->search(['status' => 'ativo']);
            $people = $this->personModel()->search([]);
            $childrenByEvent = $this->childModel()->findByEventId($id);
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao carregar lista operacional do evento.');
            Response::redirect('/delivery-events');
        }

        $deliveryForm = [
            'target_type' => 'family',
            'family_id' => 0,
            'person_id' => 0,
            'quantity' => 1,
            'observations' => '',
        ];
        $old = Session::consumeFlash('delivery_form_old');
        if (is_array($old)) {
            $deliveryForm = array_merge($deliveryForm, $old);
        }

        $autoDeliveryForm = [
            'city' => '',
            'neighborhood' => '',
            'only_pending_documentation' => 0,
            'only_needs_visit' => 0,
            'only_with_children' => 1,
            'max_income' => '',
            'limit' => 30,
            'quantity' => 1,
            'observations' => 'Gerado automaticamente por criterios',
        ];
        $autoOld = Session::consumeFlash('delivery_auto_form_old');
        if (is_array($autoOld)) {
            $autoDeliveryForm = array_merge($autoDeliveryForm, $autoOld);
        }

        View::render('delivery_events.show', [
            '_layout' => 'layouts.app',
            'appName' => (string) ($this->container->get('config')['app']['name'] ?? 'Dashboard PHP PBT'),
            'pageTitle' => 'Lista operacional de entregas',
            'activeMenu' => 'entregas',
            'authUser' => Session::get('auth_user', []),
            'event' => $event,
            'deliveries' => $deliveries,
            'families' => $families,
            'people' => $people,
            'childrenByEvent' => $childrenByEvent,
            'deliveryForm' => $deliveryForm,
            'autoDeliveryForm' => $autoDeliveryForm,
            'success' => Session::consumeFlash('success'),
            'error' => Session::consumeFlash('error'),
        ]);
    }

    public function storeDelivery(): void
    {
        $eventId = (int) ($_GET['event_id'] ?? 0);
        if ($eventId <= 0) {
            Session::flash('error', 'Evento invalido.');
            Response::redirect('/delivery-events');
        }

        $input = $this->sanitizeDeliveryInput($_POST);
        $error = $this->validateDeliveryInput($eventId, $input);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('delivery_form_old', $input);
            Response::redirect('/delivery-events/show?id=' . $eventId);
        }

        try {
            $event = $this->model()->findById($eventId);
            if ($event === null) {
                Session::flash('error', 'Evento nao encontrado.');
                Response::redirect('/delivery-events');
            }
            if ((string) ($event['status'] ?? '') === 'concluido') {
                Session::flash('error', 'Evento concluido nao permite incluir novos convidados.');
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }

            $resolved = $this->resolveDeliveryTarget((int) $eventId, $input, $event);
            if ($resolved['error'] !== null) {
                Session::flash('error', (string) $resolved['error']);
                Session::flash('delivery_form_old', $input);
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }

            if (($event['max_baskets'] ?? null) !== null) {
                $currentQty = $this->deliveryModel()->totalQuantityByEvent($eventId);
                $newQty = (int) (((array) $resolved['data'])['quantity'] ?? 0);
                if (($currentQty + $newQty) > (int) $event['max_baskets']) {
                    Session::flash('error', 'Limite de cestas do evento excedido.');
                    Session::flash('delivery_form_old', $input);
                    Response::redirect('/delivery-events/show?id=' . $eventId);
                }
            }

            $deliveryData = (array) $resolved['data'];
            $createdDeliveryId = $this->deliveryModel()->create($deliveryData);
            $this->logDeliveryOperation(
                'delivery.create',
                $createdDeliveryId,
                [
                    'event_id' => $eventId,
                    'family_id' => $deliveryData['family_id'] ?? null,
                    'person_id' => $deliveryData['person_id'] ?? null,
                    'ticket_number' => $deliveryData['ticket_number'] ?? null,
                    'quantity' => $deliveryData['quantity'] ?? null,
                    'status' => $deliveryData['status'] ?? null,
                ]
            );
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao registrar convidado/entrega na lista operacional.');
            Session::flash('delivery_form_old', $input);
            Response::redirect('/delivery-events/show?id=' . $eventId);
        }

        Session::flash('success', 'Convidado adicionado na lista operacional com senha sequencial.');
        Response::redirect('/delivery-events/show?id=' . $eventId);
    }

    public function autoGenerateDeliveries(): void
    {
        $eventId = (int) ($_GET['event_id'] ?? 0);
        if ($eventId <= 0) {
            Session::flash('error', 'Evento invalido.');
            Response::redirect('/delivery-events');
        }

        $criteria = $this->sanitizeAutoDeliveryInput($_POST);
        $error = $this->validateAutoDeliveryInput($criteria);
        if ($error !== null) {
            Session::flash('error', $error);
            Session::flash('delivery_auto_form_old', $criteria);
            Response::redirect('/delivery-events/show?id=' . $eventId);
        }

        try {
            $event = $this->model()->findById($eventId);
            if ($event === null) {
                Session::flash('error', 'Evento nao encontrado.');
                Response::redirect('/delivery-events');
            }
            if ((string) ($event['status'] ?? '') === 'concluido') {
                Session::flash('error', 'Evento concluido nao permite incluir novos convidados.');
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }

            $candidates = $this->deliveryModel()->findFamilyCandidates($criteria);
            if (empty($candidates)) {
                Session::flash('error', 'Nenhuma familia encontrada para os criterios informados.');
                Session::flash('delivery_auto_form_old', $criteria);
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }

            $maxBaskets = $event['max_baskets'] ?? null;
            $currentQty = $this->deliveryModel()->totalQuantityByEvent($eventId);
            $nextTicket = $this->deliveryModel()->nextTicketNumber($eventId);

            $quantity = (int) ($criteria['quantity'] ?? 1);
            $created = 0;
            $skippedAlreadyInEvent = 0;
            $skippedMonthBlock = 0;
            $skippedMaxBaskets = 0;

            foreach ($candidates as $candidate) {
                $familyId = (int) ($candidate['id'] ?? 0);
                if ($familyId <= 0) {
                    continue;
                }

                if ($this->deliveryModel()->existsFamilyInEvent($eventId, $familyId)) {
                    $skippedAlreadyInEvent++;
                    continue;
                }

                if ((int) ($event['block_multiple_same_month'] ?? 0) === 1) {
                    $alreadyInMonth = $this->deliveryModel()->existsFamilyDeliveryInMonth(
                        $familyId,
                        (string) ($event['event_date'] ?? date('Y-m-d')),
                        $eventId
                    );
                    if ($alreadyInMonth) {
                        $skippedMonthBlock++;
                        continue;
                    }
                }

                if ($maxBaskets !== null && ($currentQty + $quantity) > (int) $maxBaskets) {
                    $skippedMaxBaskets++;
                    continue;
                }

                $deliveryData = [
                    'event_id' => $eventId,
                    'family_id' => $familyId,
                    'person_id' => null,
                    'ticket_number' => $nextTicket,
                    'document_id' => (string) (($candidate['cpf_responsible'] ?? '') ?: ($candidate['rg_responsible'] ?? '')) ?: null,
                    'observations' => trim((string) ($criteria['observations'] ?? '')) ?: null,
                    'status' => 'nao_veio',
                    'quantity' => $quantity,
                    'delivered_at' => null,
                    'delivered_by' => null,
                    'signature_name' => null,
                ];

                $createdDeliveryId = $this->deliveryModel()->create($deliveryData);
                $this->logDeliveryOperation(
                    'delivery.create_auto',
                    $createdDeliveryId,
                    [
                        'event_id' => $eventId,
                        'family_id' => $familyId,
                        'ticket_number' => $nextTicket,
                        'quantity' => $quantity,
                        'criteria' => $criteria,
                    ]
                );

                $created++;
                $nextTicket++;
                $currentQty += $quantity;
            }

            $this->logAutoGenerationSummary($eventId, [
                'criteria' => $criteria,
                'candidate_total' => count($candidates),
                'created' => $created,
                'skipped_already_in_event' => $skippedAlreadyInEvent,
                'skipped_month_block' => $skippedMonthBlock,
                'skipped_max_baskets' => $skippedMaxBaskets,
            ]);

            if ($created === 0) {
                Session::flash(
                    'error',
                    'Nenhum convidado foi gerado. Verifique duplicidade, bloqueio mensal e limite de cestas.'
                );
                Session::flash('delivery_auto_form_old', $criteria);
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }

            Session::flash(
                'success',
                sprintf(
                    'Geracao automatica concluida: %d convidados adicionados (%d duplicados, %d bloqueio mensal, %d limite de cestas).',
                    $created,
                    $skippedAlreadyInEvent,
                    $skippedMonthBlock,
                    $skippedMaxBaskets
                )
            );
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha na geracao automatica de convidados.');
            Session::flash('delivery_auto_form_old', $criteria);
            Response::redirect('/delivery-events/show?id=' . $eventId);
        }

        Response::redirect('/delivery-events/show?id=' . $eventId);
    }

    public function updateDeliveryStatus(): void
    {
        $eventId = (int) ($_GET['event_id'] ?? 0);
        $deliveryId = (int) ($_GET['id'] ?? 0);
        $targetStatus = trim((string) ($_POST['target_status'] ?? ''));
        $signatureName = trim((string) ($_POST['signature_name'] ?? ''));

        if ($eventId <= 0 || $deliveryId <= 0) {
            Session::flash('error', 'Entrega invalida.');
            Response::redirect('/delivery-events');
        }

        try {
            $delivery = $this->deliveryModel()->findById($deliveryId);
            if ($delivery === null || (int) ($delivery['event_id'] ?? 0) !== $eventId) {
                Session::flash('error', 'Registro de entrega nao encontrado.');
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }
            $event = $this->model()->findById($eventId);
            if ($event !== null && (string) ($event['status'] ?? '') === 'concluido') {
                Session::flash('error', 'Evento concluido nao permite alterar status das entregas.');
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }

            $current = (string) ($delivery['status'] ?? 'nao_veio');
            $flowError = $this->validateDeliveryStatusTransition($current, $targetStatus, $signatureName);
            if ($flowError !== null) {
                Session::flash('error', $flowError);
                Response::redirect('/delivery-events/show?id=' . $eventId);
            }

            $authUser = Session::get('auth_user', []);
            $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : null;

            $isRetirou = $targetStatus === 'retirou';
            $this->deliveryModel()->updateStatus($deliveryId, [
                'status' => $targetStatus,
                'delivered_at' => $isRetirou ? date('Y-m-d H:i:s') : ($targetStatus === 'presente' ? null : ($delivery['delivered_at'] ?? null)),
                'delivered_by' => $isRetirou ? $userId : ($targetStatus === 'presente' ? null : ($delivery['delivered_by'] ?? null)),
                'signature_name' => $isRetirou ? $signatureName : ($delivery['signature_name'] ?? null),
            ]);

            $this->logDeliveryOperation(
                'delivery.status_update',
                $deliveryId,
                [
                    'event_id' => $eventId,
                    'from_status' => $current,
                    'to_status' => $targetStatus,
                    'family_id' => $delivery['family_id'] ?? null,
                    'person_id' => $delivery['person_id'] ?? null,
                    'ticket_number' => $delivery['ticket_number'] ?? null,
                    'signature_name' => $targetStatus === 'retirou' ? $signatureName : null,
                ]
            );
        } catch (Throwable $exception) {
            Session::flash('error', 'Falha ao atualizar status da entrega.');
            Response::redirect('/delivery-events/show?id=' . $eventId);
        }

        Session::flash('success', 'Status da entrega atualizado para ' . $targetStatus . '.');
        Response::redirect('/delivery-events/show?id=' . $eventId);
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

    private function deliveryModel(): DeliveryModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new DeliveryModel($pdo);
    }

    private function familyModel(): FamilyModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new FamilyModel($pdo);
    }

    private function childModel(): ChildModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new ChildModel($pdo);
    }

    private function personModel(): PersonModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new PersonModel($pdo);
    }

    private function auditLogModel(): AuditLogModel
    {
        /** @var PDO $pdo */
        $pdo = $this->container->get('db');
        return new AuditLogModel($pdo);
    }

    private function sanitizeDeliveryInput(array $post): array
    {
        return [
            'target_type' => trim((string) ($post['target_type'] ?? 'family')),
            'family_id' => (int) ($post['family_id'] ?? 0),
            'person_id' => (int) ($post['person_id'] ?? 0),
            'quantity' => max(1, (int) ($post['quantity'] ?? 1)),
            'observations' => trim((string) ($post['observations'] ?? '')),
        ];
    }

    private function sanitizeAutoDeliveryInput(array $post): array
    {
        $maxIncomeRaw = str_replace(',', '.', trim((string) ($post['max_income'] ?? '')));
        return [
            'city' => trim((string) ($post['city'] ?? '')),
            'neighborhood' => trim((string) ($post['neighborhood'] ?? '')),
            'only_pending_documentation' => isset($post['only_pending_documentation']) ? 1 : 0,
            'only_needs_visit' => isset($post['only_needs_visit']) ? 1 : 0,
            'only_with_children' => isset($post['only_with_children']) ? 1 : 0,
            'max_income' => $maxIncomeRaw,
            'limit' => max(1, min(500, (int) ($post['limit'] ?? 30))),
            'quantity' => max(1, min(10, (int) ($post['quantity'] ?? 1))),
            'observations' => trim((string) ($post['observations'] ?? '')),
        ];
    }

    private function validateDeliveryInput(int $eventId, array $input): ?string
    {
        if ($eventId <= 0) {
            return 'Evento invalido.';
        }
        if (!in_array((string) ($input['target_type'] ?? ''), ['family', 'person'], true)) {
            return 'Tipo de convidado invalido.';
        }
        if ($input['target_type'] === 'family' && (int) ($input['family_id'] ?? 0) <= 0) {
            return 'Selecione uma familia para convidado manual.';
        }
        if ($input['target_type'] === 'person' && (int) ($input['person_id'] ?? 0) <= 0) {
            return 'Selecione uma pessoa acompanhada para convidado manual.';
        }
        if ((int) ($input['quantity'] ?? 0) <= 0) {
            return 'Quantidade de cestas deve ser maior que zero.';
        }
        return null;
    }

    private function validateAutoDeliveryInput(array $criteria): ?string
    {
        if ((int) ($criteria['limit'] ?? 0) <= 0) {
            return 'Limite de familias para geracao automatica invalido.';
        }

        if ((int) ($criteria['quantity'] ?? 0) <= 0) {
            return 'Quantidade de cestas por familia deve ser maior que zero.';
        }

        $maxIncome = trim((string) ($criteria['max_income'] ?? ''));
        if ($maxIncome !== '' && !is_numeric($maxIncome)) {
            return 'Renda maxima deve ser numerica.';
        }

        return null;
    }

    private function resolveDeliveryTarget(int $eventId, array $input, array $event): array
    {
        $ticket = $this->deliveryModel()->nextTicketNumber($eventId);
        $familyId = null;
        $personId = null;
        $documentId = null;

        if ($input['target_type'] === 'family') {
            $family = $this->familyModel()->findById((int) $input['family_id']);
            if ($family === null) {
                return ['error' => 'Familia nao encontrada.', 'data' => null];
            }
            $familyId = (int) $family['id'];
            if ($this->deliveryModel()->existsFamilyInEvent($eventId, $familyId)) {
                return ['error' => 'Esta familia ja esta cadastrada na lista operacional deste evento.', 'data' => null];
            }
            $documentId = (string) (($family['cpf_responsible'] ?? '') ?: ($family['rg_responsible'] ?? ''));

            if ((int) ($event['block_multiple_same_month'] ?? 0) === 1) {
                $already = $this->deliveryModel()->existsFamilyDeliveryInMonth($familyId, (string) $event['event_date'], $eventId);
                if ($already) {
                    return ['error' => 'Bloqueio mensal ativo: esta familia ja retirou cesta em outro evento no mesmo mes.', 'data' => null];
                }
            }
        } else {
            $person = $this->personModel()->findById((int) $input['person_id']);
            if ($person === null) {
                return ['error' => 'Pessoa acompanhada nao encontrada.', 'data' => null];
            }
            $personId = (int) $person['id'];
            if ($this->deliveryModel()->existsPersonInEvent($eventId, $personId)) {
                return ['error' => 'Esta pessoa ja esta cadastrada na lista operacional deste evento.', 'data' => null];
            }
            $documentId = (string) (($person['cpf'] ?? '') ?: ($person['rg'] ?? ''));

            if ((int) ($event['block_multiple_same_month'] ?? 0) === 1) {
                $already = $this->deliveryModel()->existsPersonDeliveryInMonth($personId, (string) $event['event_date'], $eventId);
                if ($already) {
                    return ['error' => 'Bloqueio mensal ativo: esta pessoa ja retirou cesta em outro evento no mesmo mes.', 'data' => null];
                }
            }
        }

        return [
            'error' => null,
            'data' => [
                'event_id' => $eventId,
                'family_id' => $familyId,
                'person_id' => $personId,
                'ticket_number' => $ticket,
                'document_id' => $documentId !== '' ? $documentId : null,
                'observations' => $input['observations'] !== '' ? $input['observations'] : null,
                'status' => 'nao_veio',
                'quantity' => (int) $input['quantity'],
                'delivered_at' => null,
                'delivered_by' => null,
                'signature_name' => null,
            ],
        ];
    }

    private function validateDeliveryStatusTransition(string $current, string $target, string $signatureName): ?string
    {
        $allowed = [
            'nao_veio' => ['presente'],
            'presente' => ['retirou'],
            'retirou' => [],
        ];

        if (!isset($allowed[$current]) || !in_array($target, $allowed[$current], true)) {
            return 'Transicao de status invalida. Fluxo permitido: nao_veio -> presente -> retirou.';
        }

        if ($target === 'retirou' && $signatureName === '') {
            return 'Assinatura simples obrigatoria para marcar como retirou.';
        }

        return null;
    }

    private function logDeliveryOperation(string $action, int $deliveryId, array $details): void
    {
        try {
            $authUser = Session::get('auth_user', []);
            $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;
            $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null;
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : null;

            $encoded = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->auditLogModel()->create([
                'user_id' => $userId > 0 ? $userId : null,
                'action' => $action,
                'entity' => 'deliveries',
                'entity_id' => $deliveryId,
                'ip_address' => $ip !== '' ? $ip : null,
                'user_agent' => $userAgent !== '' ? $userAgent : null,
                'details_json' => $encoded !== false ? $encoded : null,
            ]);
        } catch (Throwable $exception) {
            // Nao interrompe fluxo operacional por falha de auditoria.
        }
    }

    private function logAutoGenerationSummary(int $eventId, array $details): void
    {
        try {
            $authUser = Session::get('auth_user', []);
            $userId = is_array($authUser) ? (int) ($authUser['id'] ?? 0) : 0;
            $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null;
            $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : null;
            $encoded = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $this->auditLogModel()->create([
                'user_id' => $userId > 0 ? $userId : null,
                'action' => 'delivery.auto_generate',
                'entity' => 'delivery_events',
                'entity_id' => $eventId,
                'ip_address' => $ip !== '' ? $ip : null,
                'user_agent' => $userAgent !== '' ? $userAgent : null,
                'details_json' => $encoded !== false ? $encoded : null,
            ]);
        } catch (Throwable $exception) {
            // Nao interrompe fluxo operacional por falha de auditoria.
        }
    }
}
