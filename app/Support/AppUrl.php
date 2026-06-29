<?php

namespace App\Support;

/**
 * Normalize APP_URL for HTTP and console (SetRequestForConsole uses config app.url).
 */
final class AppUrl
{
    public static function normalize(?string $url, ?string $allowedHostsFallback = null): string
    {
        $url = trim((string) ($url ?? ''));
        $url = trim($url, " \t\n\r\0\x0B'\"");

        if ($url !== '' && self::isUsableHttpUrl($url)) {
            return rtrim($url, '/');
        }

        $fromAllowed = self::firstAllowedHost($allowedHostsFallback);
        if ($fromAllowed !== null) {
            return 'https://'.$fromAllowed;
        }

        return 'http://localhost';
    }

    /** @return list<string> */
    public static function parseAllowedHosts(?string $csv): array
    {
        $hosts = [];
        foreach (explode(',', (string) $csv) as $part) {
            $host = self::extractHost(trim($part));
            if ($host !== null) {
                $hosts[] = $host;
            }
        }

        return array_values(array_unique($hosts));
    }

    private static function firstAllowedHost(?string $csv): ?string
    {
        foreach (self::parseAllowedHosts($csv) as $host) {
            return $host;
        }

        return null;
    }

    private static function isUsableHttpUrl(string $url): bool
    {
        if (str_contains($url, '${{') || str_contains($url, '${')) {
            return false;
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        return self::extractHost($url) !== null;
    }

    private static function extractHost(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value)) {
            $host = parse_url($value, PHP_URL_HOST);
        } else {
            $host = explode('/', $value)[0];
        }

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);

        if (str_contains($host, '${') || str_contains($host, ' ') || str_contains($host, ',')) {
            return null;
        }

        if (! preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)*$/', $host)) {
            return null;
        }

        return $host;
    }
}
