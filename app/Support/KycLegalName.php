<?php

namespace App\Support;

final class KycLegalName
{
    public const SUFFIXES = ['Jr', 'Sr', 'II', 'III', 'IV', 'V'];

    /** @return array{first: ?string, middle: ?string, last: ?string, suffix: ?string} */
    public static function parseFullName(string $full): array
    {
        $full = trim(preg_replace('/\s+/', ' ', $full) ?? '');
        if ($full === '') {
            return ['first' => null, 'middle' => null, 'last' => null, 'suffix' => null];
        }

        $parts = explode(' ', $full);
        $suffix = null;
        $lastPart = end($parts);
        if ($lastPart && self::isSuffix($lastPart)) {
            $suffix = self::normalizeSuffix($lastPart);
            array_pop($parts);
        }

        if ($parts === []) {
            return ['first' => null, 'middle' => null, 'last' => $full, 'suffix' => $suffix];
        }

        $first = array_shift($parts);
        $last  = $parts !== [] ? array_pop($parts) : $first;
        $middle = $parts !== [] ? implode(' ', $parts) : null;

        return [
            'first'  => $first,
            'middle' => $middle,
            'last'   => $last,
            'suffix' => $suffix,
        ];
    }

    public static function build(?string $first, ?string $middle, ?string $last, ?string $suffix): string
    {
        $segments = array_filter([
            trim((string) $first),
            trim((string) $middle),
            trim((string) $last),
        ], fn (string $s) => $s !== '');

        $name = implode(' ', $segments);
        $suffix = self::normalizeSuffix($suffix);
        if ($suffix !== null && $suffix !== '') {
            $name = trim($name.' '.$suffix);
        }

        return $name;
    }

    public static function isSuffix(string $token): bool
    {
        $n = self::normalizeSuffix($token);

        return $n !== null && in_array($n, self::SUFFIXES, true);
    }

    public static function normalizeSuffix(?string $suffix): ?string
    {
        if ($suffix === null || trim($suffix) === '') {
            return null;
        }

        $s = trim($suffix, "., \t");
        foreach (self::SUFFIXES as $allowed) {
            if (strcasecmp($s, $allowed) === 0 || strcasecmp($s, rtrim($allowed, '.')) === 0) {
                return $allowed;
            }
        }

        return $s;
    }
}
