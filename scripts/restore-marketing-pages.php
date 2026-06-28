<?php

/**
 * Restore @section('content') HTML from RentersMaxx standalone pages (balanced tags).
 * Keeps Renpresso layout header, marketing-hero includes, and @push('scripts').
 */

$root = dirname(__DIR__);
$rmRoot = dirname($root) . '/RentersMaxx/resources/views/pages';

$pages = [
    'contact.blade.php' => [
        'after' => '<!-- ══ CONTACT TYPE TABS ══ -->',
        'before' => '<!-- ══ CTA ══ -->',
        'append' => "\n@include('partials.sections.cta-banner', [\n  'title' => 'Ready to get started?',\n  'body' => 'Join the waitlist and be first when we launch in your country.',\n  'href' => url('/waitlist'),\n  'label' => 'Join the waitlist →',\n  'innerClass' => 'contact-cta-inner',\n])\n",
    ],
    'features.blade.php' => [
        'after' => '<!-- ══ STICKY TOGGLE ══ -->',
        'before' => "@include('partials.footer')",
    ],
    'how-it-works.blade.php' => [
        'after' => '<!-- ══ OVERVIEW STRIP ══ -->',
        'before' => "@include('partials.footer')",
    ],
    'countries.blade.php' => [
        'after' => '<!-- ══ STAT STRIP ══ -->',
        'before' => "@include('partials.footer')",
    ],
    'waitlist.blade.php' => [
        'after' => '<!-- ══ MAIN ══ -->',
        'before' => "@include('partials.footer')",
    ],
];

function extractBetween(string $html, string $after, string $before): string
{
    $start = strpos($html, $after);
    $end = strpos($html, $before);
    if ($start === false || $end === false || $end <= $start) {
        throw new RuntimeException("Markers not found: after=$after before=$before");
    }

    return trim(substr($html, $start, $end - $start));
}

function extractHeader(string $local): string
{
    if (! preg_match('/^(.*?@section\(\'content\'\)\s*\n)/s', $local, $m)) {
        throw new RuntimeException('Could not parse content header');
    }

    $header = $m[1];

    // Preserve marketing-hero partial if present (restore replaces body only).
    if (preg_match('/@include\(\'partials\.sections\.marketing-hero\'.*?\]\)\s*\n/s', $local, $hero)) {
        if (! str_contains($header, 'marketing-hero')) {
            $header .= "\n" . trim($hero[0]) . "\n";
        }
    }

    return $header;
}

function extractTail(string $local): string
{
    if (! preg_match('/(\n@endsection\s*\n.*)$/s', $local, $m)) {
        return "\n@endsection\n";
    }

    return $m[1];
}

function cleanRestored(string $body): string
{
    $body = preg_replace('/\sstyle="display:\s*none"/', ' class="is-hidden"', $body);
    $body = preg_replace('/\sstyle="padding:\s*180px\s*32px;"/', '', $body);
    $body = str_replace('<section class="story">', '<section class="story story--hero">', $body);
    $body = preg_replace('/<div style="text-align:center; reveal">/', '<div class="reveal u-text-center">', $body);
    $body = preg_replace('/<p style="font-size:23px[^"]*">Before you write<\/p>/', '<p class="contact-faq-eyebrow">Before you write</p>', $body);
    $body = preg_replace('/<h2 style="font-family:[^"]*">Common questions answered\.<\/h2>/', '<h2 class="contact-faq-title">Common questions answered.</h2>', $body);
    $body = str_replace('<p style="margin-top:24px;font-size:13px;color:var(--text-light)">', '<p class="listing-disclaimer">', $body);

    return $body;
}

foreach ($pages as $file => $markers) {
    $localPath = "$root/resources/views/pages/$file";
    $rmPath = "$rmRoot/$file";
    if (! is_file($localPath) || ! is_file($rmPath)) {
        echo "SKIP $file (missing source)\n";
        continue;
    }

    $local = file_get_contents($localPath);
    $rm = file_get_contents($rmPath);
    $body = cleanRestored(extractBetween($rm, $markers['after'], $markers['before']));
    if (! empty($markers['append'])) {
        $body .= $markers['append'];
    }
    $out = extractHeader($local) . "\n" . $body . extractTail($local);
    file_put_contents($localPath, $out);
    echo "RESTORED $file\n";
}

// About: hero from RM + story..principles from RM
$aboutLocal = "$root/resources/views/pages/about.blade.php";
$aboutRm = "$rmRoot/about.blade.php";
if (is_file($aboutLocal) && is_file($aboutRm)) {
    $local = file_get_contents($aboutLocal);
    $rm = file_get_contents($aboutRm);
    $hero = extractBetween($rm, '<!-- ══ HERO ══ -->', '<!-- ══ STORY ══ -->');
    $hero = preg_replace('/<section class="story"/', '<section class="story story--hero"', $hero);
    $rest = extractBetween($rm, '<!-- ══ STORY ══ -->', "@include('partials.footer')");
    $rest = cleanRestored($rest);
    $out = extractHeader($local) . "\n" . trim($hero) . "\n\n" . $rest . extractTail($local);
    file_put_contents($aboutLocal, $out);
    echo "RESTORED about.blade.php\n";
}

// Pricing: keep header through pricing-band, restore calculator..comparison from RM, keep faq partial
$pricingLocal = "$root/resources/views/pages/pricing.blade.php";
$pricingRm = "$rmRoot/pricing.blade.php";
if (is_file($pricingLocal) && is_file($pricingRm)) {
    $local = file_get_contents($pricingLocal);
    $rm = file_get_contents($pricingRm);
    if (! preg_match('/^(.*?@include\(\'partials\.sections\.pricing-band\'.*?\]\)\s*\n)/s', $local, $head)) {
        throw new RuntimeException('pricing-band include not found');
    }
    if (! preg_match('/(@include\(\'partials\.sections\.faq\'.*?\]\)\s*\n@endsection.*)$/s', $local, $tail)) {
        throw new RuntimeException('faq include not found');
    }
    $middle = extractBetween($rm, '<!-- ══ CALCULATOR ══ -->', '<!-- ══ FAQ ══ -->');
    $out = $head[1] . "\n" . trim($middle) . "\n\n" . $tail[1];
    file_put_contents($pricingLocal, $out);
    echo "RESTORED pricing.blade.php\n";
}

echo "Done.\n";
