<?php

$files = glob(__DIR__ . '/../resources/views/pages/*.blade.php');
foreach ($files as $f) {
    $html = file_get_contents($f);
    $opens = substr_count($html, '<div') + substr_count($html, '<section') + substr_count($html, '<form');
    $closes = substr_count($html, '</div>') + substr_count($html, '</section>') + substr_count($html, '</form>');
    $diff = $opens - $closes;
    if ($diff !== 0) {
        echo basename($f) . ": open=$opens close=$closes diff=$diff\n";
    }
}
