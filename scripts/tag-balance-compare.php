<?php

function balance(string $path): ?array {
    if (! is_file($path)) {
        return null;
    }
    $html = file_get_contents($path);
    $opens = substr_count($html, '<div') + substr_count($html, '<section') + substr_count($html, '<form');
    $closes = substr_count($html, '</div>') + substr_count($html, '</section>') + substr_count($html, '</form>');

    return ['diff' => $opens - $closes, 'lines' => substr_count($html, "\n") + 1];
}

$pages = glob(__DIR__ . '/../resources/views/pages/*.blade.php');
foreach ($pages as $local) {
    $name = basename($local);
    $remote = __DIR__ . '/../../RentersMaxx/resources/views/pages/' . $name;
    $l = balance($local);
    $r = balance($remote);
    if ($l['diff'] === 0 && ($r === null || $r['diff'] === 0)) {
        continue;
    }
    $rm = $r === null ? 'n/a' : (string) $r['diff'];
    echo "$name  local={$l['diff']}  rentersmaxx=$rm\n";
}
