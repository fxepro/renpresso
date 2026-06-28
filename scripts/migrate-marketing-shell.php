<?php

/**
 * One-off: strip legacy standalone HTML from marketing Blade pages.
 * Usage: php scripts/migrate-marketing-shell.php
 */

$base = dirname(__DIR__) . '/resources/views/pages';

function extractNavBody(string $raw): ?string
{
    if (! preg_match("/@include\('partials\.nav'.*?\)\s*\n(.*?)@include\('partials\.footer'\)/s", $raw, $m)) {
        return null;
    }

    return trim(preg_replace('/^\s*<\/div>\s*<\/div>\s*$/m', '', trim($m[1])));
}

function extractBodyContent(string $raw): ?string
{
    if (! preg_match('/<body>\s*(.*?)<\/body>/s', $raw, $m)) {
        return null;
    }

    return trim($m[1]);
}

function extractPhpPreamble(string $raw): string
{
    if (preg_match('/^@php.*?@endphp\s*/s', $raw, $m)) {
        return $m[0];
    }

    return '';
}

function wrapMarketing(string $page, string $title, string $meta, string $body, string $layout = 'marketing', array $extra = []): string
{
    $extends = "@extends('layouts.{$layout}'";
    $params = array_merge(['page' => $page], $extra);
    $pairs = [];
    foreach ($params as $k => $v) {
        $pairs[] = "'{$k}' => '{$v}'";
    }
    $extends .= ', [' . implode(', ', $pairs) . '])';

    return <<<BLADE
{$extends}

@section('title', '{$title}')
@section('meta_description', '{$meta}')

@section('content')

{$body}

@endsection

BLADE;
}

$marketing = [
    'about' => ['page' => 'about', 'title' => 'About', 'meta' => 'We built Renpresso because we lived the problem. International landlords deserve a platform built for them — not an afterthought of a US-only app.'],
    'pricing' => ['page' => 'pricing', 'title' => 'Pricing', 'meta' => 'Simple, transparent pricing for international landlords. First month free. $9 per unit per month after that. No setup fees, no contracts.'],
    'how-it-works' => ['page' => 'how-it-works', 'title' => 'How it works', 'meta' => 'Three steps to collect rent anywhere. Add a property, your tenant pays locally, you see everything in one dashboard.'],
    'features' => ['page' => 'features', 'title' => 'Features', 'meta' => 'Everything international landlords need — rent collection, maintenance, documents, tax export, and more across 60+ countries.'],
    'countries' => ['page' => 'countries', 'title' => 'Countries', 'meta' => 'Collect rent in 60+ countries with local payment rails — SEPA, UPI, BACS, ACH, Pix, and more.'],
    'waitlist' => ['page' => 'waitlist', 'title' => 'Join the waitlist', 'meta' => 'Join the Renpresso waitlist for early access. Launching first in the US, UK, France, and India.'],
    'contact' => ['page' => 'contact', 'title' => 'Contact', 'meta' => 'Get in touch with the Renpresso team. Sales, support, and partnership enquiries welcome.'],
    'listings' => ['page' => 'listings', 'title' => 'Listings', 'meta' => 'Browse long-term rentals and short-term stays from landlords on Renpresso. Filter by country or search by city.'],
    'listings-long-term' => ['page' => 'listings', 'title' => 'Long-term listings', 'meta' => 'Browse long-term rental listings from landlords on Renpresso.'],
    'listings-short-term' => ['page' => 'listings', 'title' => 'Short-term stays', 'meta' => 'Browse short-term stay listings from landlords on Renpresso.'],
    'listings-long-term-show' => ['page' => 'listings', 'title' => 'Listing', 'meta' => 'Long-term rental listing on Renpresso.'],
    'listings-short-term-show' => ['page' => 'listings', 'title' => 'Listing', 'meta' => 'Short-term stay listing on Renpresso.'],
];

foreach ($marketing as $file => $meta) {
    $path = "{$base}/{$file}.blade.php";
    if (! is_file($path)) {
        echo "SKIP missing: {$file}\n";
        continue;
    }

    $raw = file_get_contents($path);
    if (str_contains($raw, "@extends('layouts.")) {
        echo "SKIP already migrated: {$file}\n";
        continue;
    }

    $preamble = extractPhpPreamble($raw);
    $body = extractNavBody($raw);
    if ($body === null) {
        echo "FAIL no nav/footer block: {$file}\n";
        continue;
    }

    $out = $preamble . wrapMarketing($meta['page'], $meta['title'], $meta['meta'], $body);
    file_put_contents($path, $out);
    echo "OK migrated: {$file}\n";
}

// Login
$loginPath = "{$base}/login.blade.php";
$loginRaw = file_get_contents($loginPath);
if (! str_contains($loginRaw, "@extends('layouts.")) {
    $loginBody = extractBodyContent($loginRaw);
    if ($loginBody) {
        $loginBody = preg_replace('/<script src="\{\{ asset\(\'js\/app\.js\'\) \}\}"><\/script>\s*/', '', $loginBody);
        $scripts = '';
        if (preg_match('/<script>(.*?)<\/script>/s', $loginBody, $m)) {
            $scripts = trim($m[1]);
            $loginBody = preg_replace('/<script>.*?<\/script>\s*/s', '', $loginBody);
        }
        $out = <<<BLADE
@extends('layouts.auth', ['page' => 'login'])

@section('title', 'Sign in')
@section('meta_description', 'Sign in to your Renpresso account to manage your properties, collect rent, and view your dashboard.')

@section('content')

{$loginBody}

@endsection

@push('scripts')
<script>
{$scripts}
</script>
@endpush

BLADE;
        file_put_contents($loginPath, $out);
        echo "OK migrated: login\n";
    }
}

// Register pages
foreach (['register-maintenance', 'register-cleaning'] as $file) {
    $path = "{$base}/{$file}.blade.php";
    if (! is_file($path)) {
        continue;
    }
    $raw = file_get_contents($path);
    if (str_contains($raw, "@extends('layouts.")) {
        echo "SKIP already migrated: {$file}\n";
        continue;
    }
    $body = extractBodyContent($raw);
    if (! $body) {
        echo "FAIL body: {$file}\n";
        continue;
    }
    $title = str_contains($file, 'cleaning') ? 'Register cleaning team' : 'Register maintenance team';
    $out = <<<BLADE
@extends('layouts.auth', ['page' => 'register', 'registerPage' => true])

@section('title', '{$title}')
@section('meta_description', 'Register your team on Renpresso.')

@section('content')

{$body}

@endsection

BLADE;
    file_put_contents($path, $out);
    echo "OK migrated: {$file}\n";
}

echo "Done.\n";
