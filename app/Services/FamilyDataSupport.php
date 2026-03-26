<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use Throwable;

final class FamilyDataSupport
{
    public const CHRONIC_DISEASE_OPTIONS = [
        'hipertensao' => 'Hipertensao',
        'diabetes' => 'Diabetes',
        'doencas_cardiovasculares' => 'Doencas cardiovasculares',
        'obesidade' => 'Obesidade',
        'doenca_osteomuscular' => 'Doenca osteomuscular',
        'depressao_transtornos_mentais' => 'Depressao e Transtornos Mentais',
    ];

    public const SOCIAL_BENEFIT_OPTIONS = [
        'bolsa_familia' => 'Bolsa Familia',
        'bpc_loas' => 'Beneficio de Prestacao Continuada (BPC/LOAS)',
        'tarifa_social_energia' => 'Tarifa Social de Energia Eletrica',
        'aposentadoria' => 'Aposentadoria',
    ];

    public static function sanitizeRg(string $value): string
    {
        $raw = preg_replace('/[^0-9A-Z]/', '', strtoupper(trim($value)));
        if (!is_string($raw) || $raw === '') {
            return '';
        }

        $raw = substr($raw, 0, 9);
        if (strlen($raw) <= 2) {
            return $raw;
        }
        if (strlen($raw) <= 5) {
            return substr($raw, 0, 2) . '.' . substr($raw, 2);
        }
        if (strlen($raw) <= 8) {
            return substr($raw, 0, 2) . '.' . substr($raw, 2, 3) . '.' . substr($raw, 5);
        }

        return substr($raw, 0, 2) . '.' . substr($raw, 2, 3) . '.' . substr($raw, 5, 3) . '-' . substr($raw, 8, 1);
    }

    public static function sanitizePhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', trim($value));
        if (!is_string($digits) || $digits === '') {
            return '';
        }

        $digits = substr($digits, 0, 11);
        if (strlen($digits) <= 2) {
            return $digits;
        }

        if (strlen($digits) <= 10) {
            if (strlen($digits) <= 6) {
                return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2);
            }

            return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 4) . '-' . substr($digits, 6);
        }

        return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 5) . '-' . substr($digits, 7);
    }

    public static function sanitizeMoney(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '0.00';
        }

        $value = preg_replace('/[^\d,.\-]/', '', $value);
        if (!is_string($value) || $value === '') {
            return '0.00';
        }

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        $filtered = preg_replace('/[^0-9.\-]/', '', $value);
        if (!is_string($filtered) || $filtered === '') {
            return '0.00';
        }

        return number_format((float) $filtered, 2, '.', '');
    }

    public static function calculateAgeFromBirthDate(?string $birthDate): ?int
    {
        if ($birthDate === null || trim($birthDate) === '') {
            return null;
        }

        $value = trim($birthDate);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        try {
            $birth = new DateTimeImmutable($value);
            $today = new DateTimeImmutable('today');
        } catch (Throwable) {
            return null;
        }

        if ($birth > $today) {
            return null;
        }

        return (int) $birth->diff($today)->y;
    }

    public static function isAdult(?string $birthDate): bool
    {
        $age = self::calculateAgeFromBirthDate($birthDate);
        return $age !== null && $age >= 18;
    }

    public static function isRgValid(string $rg): bool
    {
        return preg_match('/^\d{2}\.\d{3}\.\d{3}-[0-9A-Z]$/', trim($rg)) === 1;
    }

    public static function buildCpfConflictMessage(array $conflict): string
    {
        $source = (string) ($conflict['source_table'] ?? '');
        $name = trim((string) ($conflict['source_name'] ?? ''));

        $target = match ($source) {
            'families' => 'familia',
            'family_members' => 'membro familiar',
            'children' => 'crianca',
            'people' => 'pessoa',
            default => 'cadastro existente',
        };

        if ($name !== '') {
            return 'CPF ja cadastrado em ' . $target . ': ' . $name . '.';
        }

        return 'CPF ja cadastrado no sistema.';
    }
}
