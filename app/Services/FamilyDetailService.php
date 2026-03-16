<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChildModel;
use App\Models\DeliveryModel;
use App\Models\EquipmentLoanModel;
use App\Models\FamilyModel;
use App\Models\VisitModel;

final class FamilyDetailService
{
    private const TABS = ['composition', 'summary', 'deliveries', 'loans', 'visits', 'pendencies'];

    public function __construct(
        private readonly FamilyModel $familyModel,
        private readonly ChildModel $childModel,
        private readonly DeliveryModel $deliveryModel,
        private readonly EquipmentLoanModel $equipmentLoanModel,
        private readonly VisitModel $visitModel,
        private readonly FamilyCompositionService $compositionService,
    ) {
    }

    public function build(int $familyId, array $query, array $flashState): array
    {
        $family = $this->familyModel->findById($familyId);
        $members = $this->familyModel->getMembersByFamilyId($familyId);
        $children = $this->childModel->findByFamilyId($familyId);
        $deliveries = $this->deliveryModel->listByFamilyId($familyId, 20);
        $loans = $this->equipmentLoanModel->listByFamilyId($familyId, 20);
        $visits = $this->visitModel->listByFamilyId($familyId, 20);

        $members = array_map(function (array $member): array {
            $personType = $this->compositionService->resolveMemberPersonType($member);
            $member['person_type'] = $personType;
            $member['type_label'] = 'Membro';
            return $member;
        }, $members);

        $requestedTab = $this->sanitizeTab((string) ($query['tab'] ?? 'composition'));
        $requestedPersonType = $this->compositionService->sanitizePersonType((string) ($query['person_type'] ?? ''), '');

        $memberEditId = (int) ($query['member_edit'] ?? 0);
        $memberEdit = null;
        foreach ($members as $member) {
            if ((int) ($member['id'] ?? 0) === $memberEditId) {
                $memberEdit = $member;
                break;
            }
        }

        $childEditId = (int) ($query['child_edit'] ?? 0);
        $childEdit = null;
        foreach ($children as $child) {
            if ((int) ($child['id'] ?? 0) === $childEditId) {
                $childEdit = $child;
                break;
            }
        }

        $memberOld = is_array($flashState['member_form_old'] ?? null) ? $flashState['member_form_old'] : null;
        $childOld = is_array($flashState['child_form_old'] ?? null) ? $flashState['child_form_old'] : null;
        $principalOld = is_array($flashState['principal_form_old'] ?? null) ? $flashState['principal_form_old'] : null;

        $principalForm = $this->compositionService->defaultPrincipalFormData($family ?? []);
        if ($principalOld !== null) {
            $principalForm = array_merge($principalForm, $principalOld);
        }

        $memberForm = $this->compositionService->defaultMemberFormData($familyId);
        if ($memberEdit !== null) {
            $memberForm = array_merge($memberForm, $memberEdit);
        }
        if ($memberOld !== null) {
            $memberForm = array_merge($memberForm, $memberOld);
        }

        $childForm = $this->compositionService->defaultChildFormData($familyId);
        if ($childEdit !== null) {
            $childForm = array_merge($childForm, $childEdit);
        }
        if ($childOld !== null) {
            $childForm = array_merge($childForm, $childOld);
        }

        $memberEditMode = $memberEdit !== null && isset($memberEdit['id']);
        $childEditMode = $childEdit !== null && isset($childEdit['id']);

        $personType = 'member';
        if ($childEditMode || $childOld !== null) {
            $personType = 'child';
        } elseif ($memberEditMode || $memberOld !== null) {
            $personType = $this->compositionService->sanitizePersonType((string) ($memberForm['person_type'] ?? ''), 'member');
        } elseif ($principalOld !== null) {
            $personType = 'principal';
        } elseif ($requestedPersonType !== '') {
            $personType = $requestedPersonType;
        }

        $openPersonForm = $memberEditMode || $childEditMode || $memberOld !== null || $childOld !== null || $principalOld !== null || $requestedPersonType !== '';
        $activeTab = $requestedTab;
        if ($openPersonForm) {
            $activeTab = 'composition';
        }

        $relationshipOptions = [
            'Filho(a)',
            'Marido',
            'Esposa',
            'Companheiro(a)',
            'Pai',
            'Mae',
            'Irmao(a)',
            'Avo(a)',
            'Neto(a)',
            'Sobrinho(a)',
            'Tio(a)',
            'Genro',
            'Nora',
            'Cunhado(a)',
            'Outro',
        ];
        $memberRelationshipCurrent = trim((string) ($memberForm['relationship'] ?? ''));
        if ($memberRelationshipCurrent === 'Dependente') {
            $memberForm['relationship'] = '';
            $memberRelationshipCurrent = '';
        }
        if ($memberRelationshipCurrent !== '' && !in_array($memberRelationshipCurrent, $relationshipOptions, true)) {
            $relationshipOptions[] = $memberRelationshipCurrent;
        }

        $addressLine = implode(' / ', array_filter([
            (string) ($family['neighborhood'] ?? ''),
            (string) ($family['city'] ?? ''),
            (string) ($family['state'] ?? ''),
        ]));

        return [
            'family' => $family,
            'members' => $members,
            'children' => $children,
            'deliveries' => $deliveries,
            'loans' => $loans,
            'visits' => $visits,
            'principalForm' => $principalForm,
            'memberForm' => $memberForm,
            'childForm' => $childForm,
            'memberEditMode' => $memberEditMode,
            'childEditMode' => $childEditMode,
            'personType' => $personType,
            'openPersonForm' => $openPersonForm,
            'activeTab' => $activeTab,
            'tabs' => self::TABS,
            'relationshipOptions' => $relationshipOptions,
            'addressLine' => $addressLine,
            'deliveryActionBaseUrl' => '/delivery-events',
        ];
    }

    private function sanitizeTab(string $value): string
    {
        $value = strtolower(trim($value));
        if (in_array($value, self::TABS, true)) {
            return $value;
        }

        return 'composition';
    }
}
