<?php

namespace App\Support;

class WorldCountries
{
    /** @return array<string, string> ISO code => display name, sorted by name */
    public static function all(): array
    {
        return config('world_countries', []);
    }

    public static function name(string $code): ?string
    {
        return self::all()[strtoupper($code)] ?? null;
    }

    public static function isValid(string $code): bool
    {
        return isset(self::all()[strtoupper($code)]);
    }

    /** @return list<string> */
    public static function codes(): array
    {
        return array_keys(self::all());
    }
}
