<?php

$src = file_get_contents(__DIR__ . '/../resources/views/partials/db-dashboard-styles.blade.php');
$css = preg_replace('/^<style>\s*|\s*<\/style>\s*$/s', '', $src);
$css = str_replace('@@media', '@media', $css);
$css = preg_replace("/@include\('partials\.db-topbar-styles'\)\s*/", '', $css);
$topbar = file_get_contents(__DIR__ . '/../resources/views/partials/db-topbar-styles.blade.php');

$out = "/* Dashboard shell — landlord, tenant, admin, portals */\n@import './tokens.css';\n\n"
    . trim($css) . "\n\n/* Topbar context */\n" . trim($topbar);

file_put_contents(__DIR__ . '/../resources/css/dashboard.css', $out);
echo strlen($out) . " bytes written to resources/css/dashboard.css\n";
