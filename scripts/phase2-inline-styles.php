<?php
/**
 * Phase 2 — replace inline style="" with utility classes in marketing pages.
 * Run: php scripts/phase2-inline-styles.php
 */

$dir = dirname(__DIR__) . '/resources/views/pages';
$files = glob($dir . '/*.blade.php');

$replacements = [
    'style="font-size:21px;color:var(--text-mid);margin-bottom:20px;font-weight:300;"' => 'class="lead-muted"',
    ' style="color:var(--white)"' => ' class="section-title--inverse"',
    ' style="text-align:center;"' => ' class="u-text-center"',
    ' style="text-align:center"' => ' class="u-text-center"',
    ' style="text-align: center;"' => ' class="u-text-center"',
    ' style="text-align: center"' => ' class="u-text-center"',
    'style="color:var(--terra-light);text-decoration:none;font-weight:500;"' => 'class="link-terra-light"',
    'style="color:var(--terra);text-decoration:none;font-weight:500;"' => 'class="link-terra"',
    'style="color:var(--terra); text-decoration:none;font-weight:500;"' => 'class="link-terra"',
    'style="color:var(--terra); font-weight:500;"' => 'class="link-terra"',
    'style="max-width:360px;"' => 'class="section-title--narrow"',
    'style="font-size:19px;margin-bottom:32px;"' => 'class="section-sub--lg"',
    'style="display:inline-flex;align-items:center;gap:6px;margin-top:28px;color:var(--terra);text-decoration:none;font-size:21px;font-weight:500;"' => 'class="link-arrow"',
    'style="background:var(--cream);border:2px dashed var(--cream-dark);"' => 'class="country-card--soon"',
    'style="align-items:center;padding-top:6px;"' => 'class="price-amount--talk"',
    'style="font-size:38px;line-height:1.15;"' => 'class="price-number--talk"',
    'style="text-align:center;margin-top:22px;font-size:19px;color:rgba(255,255,255,0.28);"' => 'class="footnote-muted"',
    'class="reveal" style="text-align:center;"' => 'class="reveal reveal--center"',
    'class="section-label" style="text-align:center;"' => 'class="section-label section-label--center"',
    'class="section-title" style="text-align:center;"' => 'class="section-title section-title--center"',
    'style="padding: 16px 0 6px;"' => 'class="price-amount--agency"',
    'style="font-size:48px; line-height:1.15;"' => 'class="price-number--talk"',
    'style="text-align:center; margin-top:24px; font-size:19px; color:var(--text-light);"' => 'class="footnote-light"',
    'style="margin-bottom:1px"' => 'class="lc-search-btn"',
    'style="font-size:18px;font-weight:500;color:var(--text-dark)"' => 'class="lc-empty-title"',
    'style="margin-top:10px;max-width:480px;margin-left:auto;margin-right:auto"' => 'class="lc-empty-sub"',
    'style="margin-top:24px;font-size:13px;color:var(--text-light)"' => 'class="listing-disclaimer"',
    'style="padding: 180px 32px;"' => 'class="story--hero"',
    'style="margin-bottom: 48px;"' => 'class="reveal--spaced"',
    'style="text-align:right"' => 'class="u-text-right login-field-actions"',
    'style="display:none;font-size:13px;color:var(--text-light);text-align:center;margin-top:8px;"' => 'class="sso-note is-hidden"',
    'style="display:block"' => 'class="form-error--block"',
    'style="display:none; text-align:center; margin-top:-8px;"' => 'class="form-error--center is-hidden"',
    'style="display:none; text-align:center; padding:20px 0;"' => 'class="reset-sent-view is-hidden"',
    'style="font-size:48px; margin-bottom:20px;"' => 'class="reset-sent-icon"',
    'style="font-size:29px; color:var(--text-dark); margin-bottom:12px;"' => 'class="reset-sent-title"',
    'style="font-size:21px; color:var(--text-mid); font-weight:300; line-height:1.6; margin-bottom:28px;"' => 'class="reset-sent-body"',
    'style="margin:0 auto; display:flex;"' => 'class="back-btn back-btn--center"',
    'style="margin-bottom:10px;"' => 'class="wl-label--spaced"',
    'style="font-size:19px;font-weight:600;color:var(--text-dark);margin-bottom:20px;"' => 'class="hiw-onboard-label"',
    'style="color:var(--terra-light)"' => 'class="dp-usd--due"',
    'style="font-size:19px;"' => 'class="section-sub--lg"',
    'style="display:block" id="tenant"' => 'id="tenant"',
    'style="text-align:right"><div class="dw-total"' => 'class="dw-total-wrap"><div class="dw-total"',
    'style="width:0%;background:rgba(255,255,255,0.15)"' => 'class="dw-bar-fill--pending"',
    'style="color:var(--terra-light)"' => 'class="dw-bar-val--due"',
    'style="background:var(--navy-mid)"' => 'class="msg-widget--navy"',
];

$complex = [
    // pricing billing panel block — handled per-file below
];

$changed = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;
    foreach ($replacements as $from => $to) {
        $content = str_replace($from, $to, $content);
    }
    if ($content !== $original) {
        file_put_contents($file, $content);
        $changed++;
        echo basename($file) . "\n";
    }
}

echo "Updated $changed files.\n";
