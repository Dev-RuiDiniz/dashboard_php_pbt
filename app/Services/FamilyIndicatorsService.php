<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ChildModel;
use App\Models\FamilyModel;

final class FamilyIndicatorsService
{
    public function __construct(
        private readonly FamilyModel $familyModel,
        private readonly ChildModel $childModel
    ) {
    }

    public function recalculate(int $familyId): void
    {
        $family = $this->familyModel->findById($familyId);
        if ($family === null) {
            return;
        }

        $members = $this->familyModel->listFamilyMembersSummary($familyId);
        $children = $this->childModel->findByFamilyId($familyId);
        $childrenCount = count($children);

        $adultsCount = FamilyDataSupport::isAdult((string) ($family['birth_date'] ?? '')) ? 1 : 0;
        $workersCount = (int) ($family['responsible_works'] ?? 0) === 1 ? 1 : 0;
        $familyIncomeTotal = (float) ($family['responsible_income'] ?? 0);
        $memberCount = 0;

        foreach ($members as $member) {
            $memberCount++;
            if ((int) ($member['works'] ?? 0) === 1) {
                $workersCount++;
            }
            if (FamilyDataSupport::isAdult((string) ($member['birth_date'] ?? ''))) {
                $adultsCount++;
            }
            $familyIncomeTotal += (float) ($member['income'] ?? 0);
        }

        foreach ($children as $child) {
            $familyIncomeTotal += (float) ($child['income'] ?? 0);
        }

        $peopleCount = 1 + $memberCount + $childrenCount;
        $familyIncomeAverage = $peopleCount > 0 ? ($familyIncomeTotal / $peopleCount) : 0.0;

        $this->familyModel->updateIndicators($familyId, [
            'adults_count' => $adultsCount,
            'workers_count' => $workersCount,
            'family_income_total' => number_format($familyIncomeTotal, 2, '.', ''),
            'family_income_average' => number_format($familyIncomeAverage, 2, '.', ''),
            'children_count' => $childrenCount,
        ]);
    }
}
