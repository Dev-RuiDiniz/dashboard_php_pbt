<?php

declare(strict_types=1);

namespace App\Services;

final class CpfService
{
    public static function digits(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $value);
        return is_string($digits) ? $digits : '';
    }

    public static function normalize(?string $value): ?string
    {
        $digits = self::digits($value);
        if ($digits === '') {
            return null;
        }

        return $digits;
    }

    public static function format(?string $value): ?string
    {
        $digits = self::normalize($value);
        if ($digits === null) {
            return null;
        }

        if (strlen($digits) !== 11) {
            return $digits;
        }

        return sprintf(
            '%s.%s.%s-%s',
            substr($digits, 0, 3),
            substr($digits, 3, 3),
            substr($digits, 6, 3),
            substr($digits, 9, 2)
        );
    }

    public static function isValid(?string $value): bool
    {
        $digits = self::normalize($value);
        if ($digits === null || strlen($digits) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += ((int) $digits[$i]) * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $digits[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }
}

